<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_number',
        'category_produk',
        'orderer_produk',
        'current_quantity',
        'price'
    ];
}
