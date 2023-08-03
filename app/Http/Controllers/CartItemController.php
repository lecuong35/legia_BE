<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Models\Bill;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->limit? $request->limit : 10;
        $cart_items = [];
        if(auth('api')->user()) {
            $cart_items = CartItem::with(['product'])->where('user_id', auth('api')->id())->where('bill_id', null)
            ->orderBy('updated_at', 'desc')->get();
        }
        else {
            $cart_items = CartItem::with(['product'])->where('bill_id', $request->bill_id)
            ->orderBy('updated_at', 'desc')->get();
        }

        return parent::get_list($cart_items);
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
    public function store(CartItemRequest $request)
    {
        $user = auth('api')->user();
        $user_id = null;
        $cart_item = [];
        if($user) {
            $user_id = $user->id;
        }
        if($request->bill_id == null && $user_id == null) return response()->json(['message' => 'Điền trường bill_id'], 500);
        $old_item = CartItem::where('product_id', $request->product_id)->where('user_id', $user_id)
                        ->where('bill_id', null)->first();
        if ($old_item != null) {
            $old_item->update([
                'quantity' => $old_item->quantity + $request->quantity,
            ]);
            if ($old_item) $cart_item = $old_item;
        }
        else {
            $new_cart_item = CartItem::create([
                'product_id' => $request->product_id,
                'bill_id' => $request->bill_id,
                'user_id' => $user_id,
                'quantity' => $request->quantity,
            ]);
            if ($new_cart_item) $cart_item = $new_cart_item;
        }

        if($cart_item) {
            return parent::success_create_update($cart_item->load(['product']));
        }
        else
            return response()->json(['message' => 'Không thành công!'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\Response
     */
    public function show(CartItem $cartItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\Response
     */
    public function edit(CartItem $cartItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $bill = Bill::find($request->bill_id);
        $cart_item = CartItem::find($id);
        $cart_item->update([
            'bill_id' => $bill->id
        ]);
        return parent::success_create_update(CartItem::with(['product'])->find($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cart_item = CartItem::where('id', $id)->first();
       if ($cart_item->bill != null) {
            if ($cart_item->bill->status != 'NEW' || $cart_item->bill->status == null) {
                return parent::error_delete();
            }
       }
       else {
            if ($cart_item->user->id == auth('api')->id()) {
                $cart_item->delete();
            }
       }
        return parent::empty_success();
    }
}
