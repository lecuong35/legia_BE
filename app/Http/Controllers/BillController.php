<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillRequest;
use App\Models\Admin;
use App\Models\Bill;
use App\Models\Product;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Notifications\NewBillNotification;
use App\Notifications\UpdateBillNotification;
use App\Notifications\VoucherNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function search(Request $request) {
        $status = $request->status;
        $bills = [];

        // check user or admin
        if(auth('api')->user()) {
            $user = auth('api')->user();
            $bills = $user->bills;
            foreach ($user->unreadNotifications as $key => $notification) {
                $notification->markAsRead();
            }
        }
        elseif(auth('admin')->user()) {
            $bills = Bill::all();
            $admin = auth('admin')->user();
            foreach ($admin->unreadNotifications as $key => $notification) {
                $notification->markAsRead();
            }
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // search by status
        if($status == 'PENDING') {
            $bills = $bills->where('status', 'PENDING');
        }
        elseif($status == 'NEW') {
            $bills = $bills->where('status', 'NEW');
        }
        elseif($status == 'APPROVED') {
            $bills = $bills->where('status', 'APPROVED');
        }
        elseif($status == 'DONE') {
            $bills = $bills->where('status', 'DONE');
        }
        elseif($status == 'CANCELLED') {
            $bills = $bills->where('status', 'CANCELLED');
        }

        if ($bills->count() == 0) {
            return response()->json(['message' => 'Khong thay don hang nao moi'], 403);
        }
        else {
            $perPage = 10;
            if($request->perPage == 'all')
                $perPage = $bills->count();
            $currentpage = \Illuminate\Pagination\Paginator::resolveCurrentPage();

            $bills = $bills->load(['voucher', 'cart_items', 'products', 'user'])->sortBy('updated_at');
            $bills = new LengthAwarePaginator(
                $bills->forPage($currentpage, $perPage),
                $bills->count(),
                $perPage,
                $currentpage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
            return parent::get_list($bills);
        }
    }

    public function store(BillRequest $request) {
        $user_id = null;
        $total = $request->total ? $request->total : 0;
        if(auth('api')->id()) $user_id = auth('api')->id();

        if ($user_id == null && $total == 0) {
            return response()->json(['message' => 'Bạn chưa chọn sản phẩm nào để đặt !'], 403);
        }
        $bill = Bill::create([
            'status' => 'NEW',
            'total' =>  $total,
            'user_id' =>  $user_id,
            'customer_phone' => $request->customer_phone,
            'voucher_id' => $request->voucher_id,
            'address' => $request->address
        ]);

        if ($total > 0) {
            $bill->update([
                'status' => 'PENDING',
            ]);
            $admin = Admin::first();
            $admin->notify(new NewBillNotification($admin));
        }

        if($bill)
            return parent::success_create_update($bill->load(['user', 'voucher']));
        else
            return response()->json(['message' => 'Không thành công !']);
    }

    public function update(Request $request) {
        $bill_id = $request->bill_id;
        $status = $request->status;
        $user = null;

        $bill = Bill::find($bill_id);
        if($bill->user_id != null) {
            $user = User::find($bill->user_id);
        }

        if($status == 'APPROVED' && $bill->status == 'PENDING') {
            $returned_products = $this->checkProductQuantity($bill);
            if(count($returned_products) > 0)
                return response()->json(['message' => 'Xác nhận không thành công !', 'missing_products' => $returned_products], 403);

            $bill->update([ 'status' => 'APPROVED']);
            if($bill->voucher_id != null) {
                UserVoucher::where('voucher_id', $bill->voucher_id)->where('user_id', $bill->user_id)->delete();
            }
            $this->updateProductQuantity($bill, 'APPROVED');

            if($user) {
                $user->notify(new UpdateBillNotification($bill->id, 'xác nhận'));
                $this->voucher_gift($user);
            };
        }
        elseif($status == 'DONE' && $bill->status == 'APPROVED') {
            $bill->update([ 'status' => 'DONE']);
            if($user) $user->notify(new UpdateBillNotification($bill->id, 'hoàn thành'));
        }
        elseif($status == 'CANCELLED') {
            if ($bill->status == 'APPROVED') {
                $this->updateProductQuantity($bill, 'CANCELLED');
            }
            $bill->update([ 'status' => 'CANCELLED']);
            if($user) $user->notify(new UpdateBillNotification($bill->id, 'hủy'));
        }
        else {
            return parent::error_update();
        }
        return parent::success_create_update(Bill::with('user', 'voucher')->find($bill_id));
    }

    public function user_cancel($id) {
        $user = auth('api')->user();
        $bill = Bill::find($id);

        if($bill->status == 'PENDING' && $bill->user_id == $user->id) {
            $bill->update([ 'status' => 'CANCELLED']);
            return response()->json(['message' => 'Hủy đơn hàng thành công !']);
        }
        else
            return parent::error_delete();
    }

    public function order(Request $request) {
        $bill_id = $request->bill_id;
        $bill = Bill::find($bill_id);
        $total = 0;

        $user = auth('api')->user();
        $user_name = 'Khách hàng ';
        $user_id = null;
        if ($user) {
            $user_name = $user->name;
            $user_id = $user->id;
            $bill = $user->bills->where('status', 'NEW')->first();
        }

        $cart_items = $bill->cart_items;
        if(count($cart_items) == 0) return response()->json(['message' => 'Chưa chọn sản phẩm nào để đặt hàng!'], 403);

        // foreach ($cart_items as $cart_item) {
        //     $total += $cart_item->product->price * $cart_item->quantity;
        // }

        // $voucher = Voucher::where('id', $request->voucher_id)->first();
        // if($voucher && in_array($voucher, [$user->vouchers])) {
        //     $total = $total * $voucher->value;
            UserVoucher::where('voucher_id', $request->voucher_id)->where('user_id', $user->id)->delete();
        // }

        $bill->update([
            'voucher_id' => $request->voucher_id,
            'total' => $request->total,
            'status' => 'PENDING',
            'user_id' => $user_id,
            'customer_phone' => $request->customer_phone,
            'address' => $request->address
        ]);

        $admin = Admin::first();
        $admin->notify(new NewBillNotification($user_name));
        return parent::success_create_update($bill->load(['voucher', 'products', 'cart_items']));
    }

    public function export_bill($id) {
        $bill = Bill::with('user', 'cart_items', 'products')->where('id', $id)->first();
        $pdf = PDF::loadView('invoice', compact('bill'));
        return $pdf->download('invoice.pdf');
    }

    public function show(Request $request, $id) {
        // if (auth('admin')->id() == null && auth('api')->id() == null) {
        //     return response()->json(['message' => 'Chua xac thuc'], 403);
        // }

        $bill = Bill::with(['cart_items', 'user', 'products', 'voucher'])->where('id', $id)->first();
        if ($bill == null) {
            parent::no_record();
        }

        if (auth('admin')->id() != null){
            return response()->json($bill);
        }
        else if ( auth('api')->id() != null) {
            if (auth('api')->user()->phone == $bill->customer_phone || auth('api')->id() == $bill->user_id)
                return response()->json($bill);
        }
        else {
            return response()->json(['message' => 'Chua xac thuc'], 403);
        }
    }

    //bill history
    public function history() {
        $this->middleware('auth: admin');

        $turnover['jan'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 1)
        ->sum('total');

        $turnover['feb'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 2)
        ->sum('total');

        $turnover['mar'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 3)
        ->sum('total');

        $turnover['apr'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 4)
        ->sum('total');

        $turnover['may'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 5)
        ->sum('total');

        $turnover['jun'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 6)
        ->sum('total');

        $turnover['jul'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 7)
        ->sum('total');

        $turnover['aug'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 8)
        ->sum('total');

        $turnover['sep'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 9)
        ->sum('total');

        $turnover['oct'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 10)
        ->sum('total');

        $turnover['nov'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 11)
        ->sum('total');

        $turnover['dec'] = DB::table('bills')
        ->where('handled', '=', true)
        ->whereMonth('updated_at', '=', 12)
        ->sum('total');

        return $turnover;
    }

    private function checkProductQuantity($bill): array {
        $returned_products = [];
        foreach ($bill->cart_items as $cart_item) {
            $product = Product::where('id',$cart_item->product->id)->first();
            $missing_quantity = $cart_item->quantity - $product->quantity;
            if($missing_quantity > 0) {
                array_push($returned_products, ['product' => $product, 'missing_quantity' => $missing_quantity]);
            }
        }

        return $returned_products;
    }

    private function updateProductQuantity($bill, $status) {
        if ($status == 'APPROVED') {
            foreach ($bill->cart_items as $cart_item) {
                $product = Product::where('id',$cart_item->product->id)->first();
                $product->update([
                    'quantity' => $product->quantity - $cart_item->quantity,
                ]);
            }
        }
        else if ($status == 'CANCELLED') {
            foreach ($bill->cart_items as $cart_item) {
                $product = Product::where('id',$cart_item->product->id)->first();
                $product->update([
                    'quantity' => $product->quantity + $cart_item->quantity,
                ]);
            }
        }
    }

    private function voucher_gift($user) {
        $total = $user->bills->whereNotIn('status', ['NEW', 'PENDING'])->sum('total');

        $voucher = Voucher::where('end_date', '>', now())->where('value_condition', '<', $total)
                    ->orderBy('value_condition', 'desc')->first();


        if($voucher != null) {
            UserVoucher::firstOrCreate([
                'user_id' => $user->id,
                'voucher_id' => $voucher->id
            ]);

            $user->notify(new VoucherNotification($voucher));
        }
    }
}
