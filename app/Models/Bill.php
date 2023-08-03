<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'address', 'user_id', 'customer_phone', 'total', 'status', 'voucher_id'
    ];

    public function voucher() {
        return $this->belongsTo(Voucher::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function cart_items() {
        return $this->hasMany(CartItem::class);
    }

    public function products() {
        return $this->belongsToMany(Product::class, 'cart_items', 'bill_id', 'product_id');
    }

}
