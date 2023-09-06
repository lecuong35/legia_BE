<?php

namespace App\Imports;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\AddProductService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductsImport implements ToCollection
{
    public $not_have = [];
    public $updated = [];
    protected $addProductService;

    public function __construct(AddProductService $addProductService)
    {
        $this->addProductService = $addProductService;
    }

    public function collection(Collection $rows)
    {
        $count = 0;
        foreach ($rows as $key => $row) {
            if($key > 0 && $row[0] !== null) {
                $product = Product::where('name', $row[1])->first();
                if($product) {
                    $product->update([
                        'quantity' => $row[3] + $product->quantity,
                    ]);
                    array_push($this->updated, ["name" => $row[1], "quantity" => $row[3]]);
                }
                else {
                    $category_name = explode('-', $row[5])[1];
                    $brand_name = explode('-', $row[4])[1];

                    $category =  Category::firstOrCreate([
                            'name' => 'Phụ tùng ' . $category_name,
                        ]);


                    $brand = Brand::firstOrCreate([
                            'name' => $brand_name,
                        ]);

                    $controller = new Controller();
                    $image = $controller->show_images(['logo_no_background.png']);
                    // $this->addProductService($name)
                }
            }
        }
    }
}
