<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';
    protected $fillable = [
        'user_id',
        'product_name',
        'category_id',
        'sub_category_id',
        'price',
        'minimum_order',
        'preorder',
        'duration_unit',
        'duration',
        'description',
        'video_url',
        'product_weight',
        'weight_unit',
        'size_unit',
        'height',
        'length',
        'width',
        'rate',
        'size_guide',
        'discount_type',
        'discount',
        'status'
    ];

    protected static function booted()
    {
        static::deleted(function($product) {
            $product->product_combination()->delete();
            $product->product_image()->delete();
            
            foreach($product->product_variant_option as $product_variant_option) {
                $product_variant_option->product_variant_option_value_many()->delete();
            }
            $product->product_variant_option()->delete();
            $product->user_wishlist()->delete();
            $product->product_slider()->delete();
        });
    }

    public function getCreatedAtAttribute($date) {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date) {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function product_image()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('order', 'asc');
    }

    public function product_variant_option()
    {
        return $this->hasMany(ProductVariantOption::class, 'product_id');
    }

    public function product_combination()
    {
        return $this->hasMany(ProductCombination::class, 'product_id');
    }

    public function category ()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sub_category ()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function user_wishlist() {
        return $this->hasMany(UserWishlist::class, 'product_id');
    }

    public function product_slider()
    {
        return $this->hasOne(ProductSlider::class, 'product_id');
    }
}
