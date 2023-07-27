<?php

namespace App\Http\Resources\API\Taboola;

use App\Http\Resources\API\CountryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
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
            'name' => $this->name,
            'url' => $this->domain,
            'partnership' => $this->partnership,
            'status' => $this->status,
            'countries' => CountryResource::collection($this->whenLoaded('countries')),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
