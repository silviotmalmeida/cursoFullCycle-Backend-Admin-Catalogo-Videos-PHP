<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class GenreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // ajustando as categorias:
        $categories_id = [];
        if (isset($this->categories_id)) {
            $categories_id = $this->categories_id;
        } else if (isset($this->categoriesId)) {
            $categories_id = $this->categoriesId;
        }
        else if (isset($this->categories)) {
            $categories_id = Arr::pluck($this->categories, 'id');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'categories_id' => $categories_id,
            'created_at' => Carbon::make($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::make($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
