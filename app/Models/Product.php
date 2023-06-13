<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];
    // public function setCreatedAtAttribute($value)
    // {        
    //     $this->attributes['created_at']=Carbon::createFromFormat('d/m/Y H:i:s',$value)->format('Y-m-d H:i:s');
    // }
    public function getCreatedAtAttribute()
    {
        if ($this->attributes['created_at']!=null) {
            return Carbon::createFromFormat('Y-m-d H:i:s',$this->attributes['created_at'])->format('j-M-Y');
        }
    }

    public function product_variants(){
        return $this->hasMany(ProductVariant::class);
    }
    public function product_variant_prices(){
        return $this->hasMany(ProductVariantPrice::class);
    }
    public function product_images(){
        return $this->hasMany(ProductImage::class);
    }

}
