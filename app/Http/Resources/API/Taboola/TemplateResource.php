<?php

namespace App\Http\Resources\API\Taboola;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\CountryResource;
use App\Http\Resources\API\LanguageResource;

class TemplateResource extends JsonResource
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
            'description' => $this->description,
            'template'    => $this->template,
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'category' =>  $this->category,
            'countries' => CountryResource::collection($this->whenLoaded('countries')),
        ];
    }
}
