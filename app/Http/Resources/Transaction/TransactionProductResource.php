<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_slug' => $this->product_slug,
            'image' => $this->image,
            'product_name' => $this->product_name,
            'discount_group' => $this->discount_group,
            'discount_customer' => $this->discount_customer,
            'discount_product' => $this->discount_product,
            'price' => $this->price,
            'new_price' => $this->new_price,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
        ];
    }
}
