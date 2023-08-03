<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $brands = Brand::with(['products'])->orderBy('updated_at', 'desc')->get();
        return parent::get_list($brands);
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
    public function store(BrandRequest $request)
    {
        $brand = Brand::create([
            'name' => $request->name
        ]);
        return parent::success_create_update($brand->load('products'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $brand = Brand::where('id', $id)->first();
        if( $brand === null)
            return parent::no_record();
        else
            return parent::get_list($brand);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(BrandRequest $request, $id)
    {
        $updatable = true;
        $product = Product::where('brand_id', $id)->first();

        if($product) {
            foreach ($product->bills as $key => $value) {
                if(in_array($value->status, ['PENDING', 'APPROVED'])) {
                    $updatable = false;
                    break;
                }
            }
        }

        if($updatable) {
            $updated = Brand::find($id)->update([
                'name' => $request->name,
                'updated_at' => now()
            ]);
            if ($updated) {
                $data = Brand::with(['products'])->where('id', $id)->get();
                return parent::success_create_update($data);
            }
        }
        else {
            return parent::error_update();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $updatable = true;
        $product = Product::where('brand_id', $id)->first();

        if($product) {
            foreach ($product->bills as $key => $value) {
                if(in_array($value->status, ['PENDING', 'APPROVED'])) {
                    $updatable = false;
                    break;
                }
            }
        }

        if($updatable) {
            Brand::find($id)->delete();
            return parent::empty_success();
        }
        else {
            return parent::error_delete();
        }
    }
}
