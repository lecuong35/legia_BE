<?php

namespace App\Http\Controllers;

use App\Exports\ProductExport;
use App\Http\Requests\AddProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Imports\ProductsImport;
use App\Models\CartItem;
use App\Models\Product;
use COM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use function PHPSTORM_META\map;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $per_page = 12;
        $option = $request->option;
        if($request->limit)
            $per_page = $request->limit;
        $products = Product::with(['brand', 'category'])->orderBy('updated_at', 'desc')->paginate($per_page);
        if($option == 'good') {
            $products = Product::with(['brand', 'category'])->orderBy('price', 'desc')->paginate($per_page);
        }
        return parent::get_list($products);
    }

    public function store(AddProductRequest $request) {
        $file_names = [];
        $filenames = [];
        if($request->hasFile('images')) {
            $filenames = parent::upload_images($request->file('images'), $file_names);
        }
        $file_names = parent::show_images($filenames);

        $product  = Product::firstOrCreate([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'images' => $file_names,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'quantity' => $request->quantity,
            'code' => $request->code,
        ]);

       return parent::success_create_update($product->load(['brand', 'category']));
    }

    public function show($id) {
        $product = Product::with(['brand', 'category'])->where('id', $id)->first();
        if ($product == null) {
            return parent::no_record();
        }
        return parent::success_create_update($product);
    }

    public function update(UpdateProductRequest $request, $id) {
        $cart_item = CartItem::where('product_id', $id)->first();
        if ($cart_item && in_array($cart_item->bill->status, ['PENDING', 'APPROVED'])) {
            return parent::error_update();
        }
        else {
            $file_names = [];
            $filenames = [];
            $product =  Product::find($id);
            $images = $product->images;

            if($request->delete_images) {
                $delete_images = $request->delete_images;
                $images = array_filter($images, function ($image) use ($delete_images) {
                    if (!in_array($image['name'], $delete_images))
                        return $image;
                });
            }

            if($request->hasFile('images')) {
                $filenames = parent::upload_images($request->file('images'), $file_names);
                $file_names = parent::show_images($filenames);
                if ($images == "hello") {
                    $images = $file_names;
                } else {
                    $images = array_merge($images, $file_names);
                }
            }

            $product->update([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'images' => $images,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'quantity' => $request->quantity,
                'code' => $request->code,
                'updated_at' => now()
            ]);

            $product = Product::with(['brand', 'category'])->find($id);
            return parent::success_create_update($product);
        }
    }

    public function destroy($id) {
        $statuses = ['PENDING', 'APPROVED'];
        $cart_item = CartItem::where('product_id', $id)->first();
        if ($cart_item && in_array($cart_item->bill->status, $statuses)) {
            return parent::error_update();
        }
        else {
            $product = Product::find($id)->delete();
            return parent::empty_success();
        }
    }


    // search product
    public function search(Request $request) {
        $category = $request->category;
        $brand = $request->brand;
        $code = $request->code;
        $name = $request->name;
        $limit = $request->limit? $request->limit : 12;
        $products = [];
        $products = Product::with(['brand', 'category'])
            ->where(function ($query) use ($code, $category, $brand, $name) {
                $query->where('id', '>', 0);

                if ($code) {
                    $query->where('code','LIKE', '%'. $code.'%');
                }

                if ($category) {
                    $query->where('category_id', $category);
                }

                if ($brand) {
                    $query->where('brand_id', $brand);
                }

                if ($name) {
                    $query->where('name','LIKE', '%'. $name.'%');
                }
            })
            ->orderBy('updated_at', 'desc')->paginate($limit);

        return parent::get_list($products);
    }

    // xuat file bao gia
    public function exportIntoExcel() {
        return Excel::download(new ProductExport, 'bangbaogia.xlsx');
    }

    // nhap hang
    public function importExcelFile(Request $request) {
        if($request->hasFile('product')) {
            $import = new ProductsImport;
            Excel::import($import, $request->file('product'));
            return response()->json([
                'message' => 'updated successfully',
                'imported' => $import->updated,
                'not_have' => $import->not_have,
            ], 200);
        }
    }
}
