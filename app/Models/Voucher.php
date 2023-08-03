<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'value', 'start_date', 'end_date', 'value_condition'
    ];

    public function bills() {
        return $this->hasMany(Bill::class);
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }
}
