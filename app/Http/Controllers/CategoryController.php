<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseFormatSame;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::with(['products'])->orderBy('updated_at', 'desc')->get();
        return parent::get_list($categories);
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
    public function store(AddCategoryRequest $request)
    {
        $category = Category::create([
            'name' => $request->name
        ]);

        return parent::success_create_update($category->load('products'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);
        if ($category === null)
            return parent::no_record();
        else
            return parent::success_create_update($category);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AddCategoryRequest $request, $id)
    {
        $product = Product::where('brand_id', $id)->first();
        if ($product === null) {
            $updated = Category::find($id)->update([
                'name' => $request->name,
                'updated_at' => now(),
            ]);
            if($updated) {
                $category = Category::find($id);
                return parent::success_create_update($category);
            }
        }
        else
            return parent::error_update();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::where('brand_id', $id)->first();
        if ($product === null) {
            Category::find($id)->delete();
            return parent::empty_success();
        }
        else
            return parent::error_update();
    }
}
