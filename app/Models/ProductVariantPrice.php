<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        // 'product_id',
        'product_variant_one',
        'product_variant_two',
        'product_variant_three',
        'price',
        'stock',
    ];
    function product() {
        return $this->belongsTo(Product::class);
    }

    function product_variant_color(){
        return $this->belongsTo(ProductVariant::class,'product_variant_one');
    }
    function product_variant_size(){
        return $this->belongsTo(ProductVariant::class,'product_variant_two');
    }
    function product_variant_style(){
        return $this->belongsTo(ProductVariant::class,'product_variant_three');
    }
}
