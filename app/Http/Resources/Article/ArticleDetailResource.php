<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request)
    {
        $data['id'] = $this->id;
        $data['slug'] = $this->slug;
        $data['type'] = $this->type;
        $data['title'] = $this->title;

        if($this->type == 'article') {
            $data['image_url'] = $this->image_url;
        }

        if($this->type == 'video') {
            $data['video_url'] = $this->video_url;
        }

        $data['content'] = $this->content;
        $data['created_at'] = $this->created_at;
        $data['updated_at'] = $this->updated_at;

        return $data;
    }
}
