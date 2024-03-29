<?php

namespace App\Http\Controllers\API\Product;

use App\Helpers\FileHelpers;
use App\Helpers\ResponseFormatter;
use App\Helpers\StrHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCombinationResource;
use App\Http\Resources\Product\ProductDetailResource;
use App\Models\Product;
use App\Models\ProductCombination;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateProductController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $request->validate([
            // info product
            'sku' => [ 'nullable', 'string' ],
            'product_name' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['required', 'exists:sub_categories,id'],
            'price' => [
                Rule::requiredIf(empty($request->variant)),
                'integer'
            ],
            'minimum_order' => ['required', 'integer', 'min:1'],
            'preorder' => ['required', 'boolean'],
            'duration_unit' => [
                Rule::requiredIf($request->preorder == 1),
                'in:day,week'
            ],
            'duration' => [
                Rule::requiredIf($request->preorder == 1),
                'integer',
                'min:1'
            ],
            'description' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'stock' => [
                Rule::requiredIf(empty($request->variant)),
                'integer'
            ],
            'product_weight' => ['required', 'integer'],
            'weight_unit' => ['required', 'in:gram,kg'],
            'size_unit' => ['nullable', 'in:cm,m'],
            'height' => [
                Rule::requiredIf(!empty($request->size_unit)), 
                'integer'
            ],
            'length' => [
                Rule::requiredIf(!empty($request->size_unit)),
                'integer'
            ],
            'width' => [
                Rule::requiredIf(!empty($request->size_unit)),
                'integer'
            ],
            'size_guide' => ['nullable', 'string'],
            'active_discount' => ['required', 'in:0,1'],
            'discount_type' => [
                Rule::RequiredIf($request->active_discount == 1),
                'in:rp,percent'
            ],
            'discount' => [
                Rule::RequiredIf($request->active_discount == 1),
                'integer'
            ],
            'status' => ['required', 'in:active,not_active'],

            // product_variant
            'variant' => ['nullable', 'array'],
            'variant.*.variant_name' => ['required_with:variant', 'exists:variants,variant_name'],
            'variant.*.variant_option' => ['required_with:variant.*.variant_name', 'array', 'exists:variant_options,variant_option_name'],

            // product combination
            'combination' => ['required_with:variant', 'array'],
            'combination.*.combination_string' => ['required_with:combination'],
            'combination.*.sku' => [ 'nullable', 'string'],
            'combination.*.price' => [ 'required_with:combination', 'integer' ],
            'combination.*.stock' => [ 'required_with:combination', 'integer' ],
            'combination.*.image' => [ 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg' ],
            'combination.*.status' => [ 'required_with:combination', 'in:active,not_active' ],
            'combination.*.main' => [ 'required_with:combination', 'boolean' ],

              // product image
            'product_image' => ['nullable', 'array'],
            'product_image.*.order' => ['required_with:product_image', 'integer'],
            'product_image.*.product_image' => [
                // Rule::requiredIf(function () use ($request, $product) {
                //     $order = $product->product_image()->where('order', $request->order)->count();
                //     return $order < 1;
                // }), 
                'nullable',
                'image', 
                'mimes:jpeg,png,jpg,gif,svg'
            ],
        ]);

        // insert product
        $input = $request->all();
        if(empty($request->variant)) {
            $input['status'] = $request->status;
        }
        if($request->active_discount == 1) {
            $input['discount'] = $request->discount;
            $input['discount_type'] = $request->discount_type;
        } else {
            $input['discount'] = null;
            $input['discount_type'] = null;
        }
        $product->update($input);

        // update Image Product
            $product_image_order =  $product->product_image()->get()->pluck('order')->toArray();
            foreach($request->product_image as $product_image) 
            {
                if(!empty($product_image['product_image'])) {
                    $path = FileHelpers::upload_file('product', $product_image['product_image']);
                    $product_images = [
                        'product_image' => $path,
                        'order' => $product_image['order']
                    ];
                } else {
                    $product_images = [
                        'order' => $product_image['order']
                    ];
                }
                if(in_array($product_image['order'], $product_image_order)) {
                    $product_image_d = $product->product_image()->where('order', $product_image['order'])->first();
                    if(!empty($product_image['product_image'])) {
                        Storage::disk('public')->delete($product_image_d['product_image']);
                    }
                    $product_image_d->update($product_images);
                } else {
                    $product->product_image()->create($product_images);
                }
                $orders[] = $product_image['order'];
            }
            $except_order = array_values(array_diff($product_image_order, $orders));
            if(!empty($except_order)) {
                $except_product_image = $product->product_image()->whereIn('order', $except_order);
                $p_imgs = $except_product_image->get()->pluck('product_image')->toArray();
                Storage::disk('public')->delete($p_imgs);
                $except_product_image->forceDelete();
            }
        // end update image product
        
        // update product variant
            $product_variant_names = $product->product_variant_option()->get()->pluck('variant_name')->toArray();
            if($request->variant) {
                foreach($request->variant as $variant) {
                    if(in_array($variant['variant_name'], $product_variant_names)) {
                        $product_variant_option = $product->product_variant_option()->where('variant_name', $variant['variant_name'])->first();
                    } else {
                        $product_variant_option = $product->product_variant_option()->create([ 'variant_name' => $variant['variant_name'] ]);
                    }
                    $product_variant_option->product_variant_option_value()->sync($variant['variant_option']);
                        $variants[] = $variant['variant_name'];
                }
                $except_variant = array_values(array_diff($product_variant_names, $variants));
                if(!empty($except_variant)) {
                    $product->product_variant_option()->whereIn('variant_name', $except_variant)->forceDelete();
                }

                // update product combination
                $unique_strings = $product->product_combination()->get()->pluck('unique_string')->toArray();
                foreach($request->combination as $combination) {
                    $string = $request->product_name.' '.$combination['combination_string'];
                    $product_slug = $this->slug_cek($string);
                    $unique_string =  Str::lower(StrHelper::sort_character(Str::replace('-', '', $combination['combination_string'])));
                    $statuses[] = $combination['status']; 
                    
                    if(!empty($combination['image'])) {
                        $image_path = FileHelpers::upload_file('product', $combination['image']);
                        $product_combination = [
                            'product_slug' => $product_slug,
                            'combination_string' => $combination['combination_string'],
                            'sku' => $combination['sku'],
                            'price' => $combination['price'],
                            'unique_string' => $unique_string,
                            'stock' => $combination['stock'],
                            'image' => $image_path,
                            'status' => $combination['status'],
                            'main' => $combination['main'],
                        ];
                    } else {
                        $product_combination = [
                            'product_slug' => $product_slug,
                            'combination_string' => $combination['combination_string'],
                            'sku' => $combination['sku'],
                            'price' => $combination['price'],
                            'unique_string' => $unique_string,
                            'stock' => $combination['stock'],
                            'status' => $combination['status'],
                            'main' => $combination['main'],
                        ];
                    }
                    if(in_array($unique_string, $unique_strings)) {
                        $new_product_combination = Arr::except($product_combination, ['product_slug']);
                        $product->product_combination()->where('unique_string', $unique_string)->first()->update($new_product_combination);
                    } else {
                        $product->product_combination()->create($product_combination);
                    }
                    $unique_string2[] = $unique_string;
                }
                $except_unique_string = array_values(array_diff($unique_strings, $unique_string2));
                if(!empty($except_unique_string)) {
                    $except_combination_image = $product->product_combination()->whereIn('unique_string', $except_unique_string);
                    $c_img = $except_combination_image->get()->pluck('image')->toArray();
                    Storage::disk('public')->delete($c_img);
                    $except_combination_image->forceDelete();
                    $product->product_combination()->whereNull('unique_string')->forceDelete();
                }
                $main_product_combination = $product->product_combination()->where('main', 1)->first();
                $product->update([ 
                    'status' => in_array('active', $statuses) ? 'active' : 'not_active',
                    'price' => $main_product_combination->price
                ]);
                // end update product combination
            } else {
                $product_combination = $product->product_combination()->first();
                $product_slug = ($product_combination->unique_string == null) ? $product_combination->product_slug : $this->slug_cek($request->product_name);
                $product_combination->update([
                    'product_slug' => $product_slug,
                    'combination_string' => null,
                    'sku' => $request->sku,
                    'price' => $request->price,
                    'unique_string' => null,
                    'stock' => $request->stock,
                    'status' => $request->status,
                    'main' => 1,
                ]);
                $product->product_combination()->where('id', '!=', $product_combination->id)->forceDelete();
                $product->product_variant_option()->forceDelete();
            }
        // end update product variant
        
        return ResponseFormatter::success(new ProductDetailResource($product), 'success update product data');
    }

    public function add_stock(Request $request, ProductCombination $product_combination)
    {
        $request->validate([
            'quantity' =>  ['required', 'integer']
        ]);
        $product_combination->update([
            'stock' => $product_combination->stock + $request->quantity
        ]);

        return ResponseFormatter::success(new ProductCombinationResource($product_combination), 'success add stock');
    }

    public function update_stock(Request $request, ProductCombination $product_combination)
    {
        $request->validate([
            'stock' =>  ['required', 'integer']
        ]);
        $product_combination->update([
            'stock' => $request->stock
        ]);

        return ResponseFormatter::success(new ProductCombinationResource($product_combination), 'success add stock');
    }

    public function update_discount(Request $request, Product $product)
    {
        $request->validate([
            'active_discount' => ['required', 'in:0,1'],
            'discount_type' => [
                Rule::RequiredIf($request->active_discount == 1),
                'in:rp,percent'
            ],
            'discount' => [
                Rule::RequiredIf($request->active_discount == 1),
                'integer'
            ],
        ]);

        if($request->active_discount == 1) {
            $discount = $request->discount;
            $discount_type = $request->discount_type;
        } else {
            $discount = null;
            $discount_type = null;
        }

        $product->update([
            'discount_type' => $discount_type,
            'discount' => $discount,
        ]);

        return ResponseFormatter::success(new ProductDetailResource($product), 'success update product discount data');
    }

    public function slug_cek($string)
    {
        $counter = 0;
        $slug = Str::slug($string);
        $original_slug = Str::slug($string);
        while(ProductCombination::withTrashed()->where('product_slug', $slug)->count() > 0) {
            $counter++;
            $slug = "{$original_slug}-{$counter}";
        }
        return $slug;
    }
}
