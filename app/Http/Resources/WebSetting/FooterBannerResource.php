<?php

namespace App\Http\Resources\WebSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FooterBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'banner' => $this->content_url,
        ];
    }
}
