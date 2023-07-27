<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
            'image_name' => $this->image_name,
            'original_image_id' => $this->original_image_id,
            'url' => $this->temporary_url,
            'path' => $this->url,
            'hash' => $this->hash,
            'size' => $this->size,
            'perceptual_hash' => $this->perceptual_hash,
            'width' => $this->width,
            'height' => $this->height,
            'mimetype' => $this->mimetype,
            'info' => $this->info,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'keywords' => KeywordResource::collection($this->whenLoaded('keywords')),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
