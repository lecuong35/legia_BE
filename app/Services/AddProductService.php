<?php

namespace App\Services;

use App\Models\Product;

class AddProductService {
    public function addProduct($name, $price, $category_id, $brand_id, $images, $code, $quantity) {
        $product = Product::create([
            'name' => $name,
            'price' => $price,
            'category_id' => $category_id,
            'brand_id' => $brand_id,
            'images' => $images,
            'code' => $code,
            'quantity' => $quantity,
        ]);

        if ($product) {
            return response()->json([
                'message' => 'Tao thanh cong',
            ]);
        }
    }
}
