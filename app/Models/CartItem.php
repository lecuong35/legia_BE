<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'quantity', 'user_id', 'bill_id'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function bill() {
        return $this->belongsTo(Bill::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
