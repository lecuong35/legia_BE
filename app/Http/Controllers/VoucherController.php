<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoucherRequest;
use App\Models\Bill;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = 10;
        if($request->limit)
            $per_page = $request->limit;
        $vouchers = DB::table('vouchers')->orderBy('updated_at', 'desc')->paginate($per_page);

        if($request->name) {
            $vouchers = DB::table('vouchers')
            ->where('name', 'LIKE', '%'.$request->name.'%')
            ->orderBy('updated_at', 'desc')->paginate($per_page);
        }
        return parent::get_list($vouchers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VoucherRequest $request)
    {
        $voucher = Voucher::create([
            'name' => $request->name,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'value_condition' => $request->value_condition
        ]);
        return parent::success_create_update($voucher);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $voucher = Voucher::find($id);
        if ($voucher)
            return parent::success_create_update($voucher);
        else
            return parent::no_record();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function edit(Voucher $voucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function update(VoucherRequest $request, $id)
    {
       $bill = Bill::where('voucher_id', $id)->first();
       if($bill) return parent::error_update();
       else {
            $voucher = Voucher::find($id);
            if ($voucher) {
                $voucher->update([
                    'name' => $request->name,
                    'value' => $request->value,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'value_condition' => $request->value_condition
                ]);
                $voucher = Voucher::find($id);
                return parent::success_create_update($voucher);
            }
            else
                return parent::no_record();
       }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bill = Bill::where('voucher_id', $id)->first();
        if($bill) return parent::error_delete();
        else {
            $voucher = Voucher::find($id);
            if($voucher) {
                $voucher->delete();
                return parent::empty_success();
            }
            else
                return parent::no_record();
        }
    }

    public function user_voucher() {
        $user = auth('api')->user();
        $vouchers = $user->vouchers->where('end_date', '>', now())->orderBy('end_date', 'DESC')->get();
        return parent::get_list($vouchers);
    }
}
