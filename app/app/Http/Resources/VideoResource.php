<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // ajustando os dados de entrada:
        $year_launched = isset($this->year_launched) ? $this->year_launched : $this->yearLaunched;

        if(isset($this->categories_id)) $categories_id = $this->categories_id;
        else if(isset($this->categoriesId)) $categories_id = $this->categoriesId;
        else $categories_id = [];

        if(isset($this->genres_id)) $genres_id = $this->genres_id;
        else if(isset($this->genresId)) $genres_id = $this->genresId;
        else $genres_id = [];

        if(isset($this->cast_members_id)) $cast_members_id = $this->cast_members_id;
        else if(isset($this->castMembersId)) $cast_members_id = $this->castMembersId;
        else $cast_members_id = [];

        if(isset($this->thumbfile)) $thumbfile = $this->thumbfile;
        else if(isset($this->thumbFile)) $thumbfile = $this->thumbFile;
        else $thumbfile = null;

        if(isset($this->thumbhalf)) $thumbhalf = $this->thumbhalf;
        else if(isset($this->thumbHalf)) $thumbhalf = $this->thumbHalf;
        else $thumbhalf = null;

        if(isset($this->bannerfile)) $bannerfile = $this->bannerfile;
        else if(isset($this->bannerFile)) $bannerfile = $this->bannerFile;
        else $bannerfile = null;

        if(isset($this->trailerfile)) $trailerfile = $this->trailerfile;
        else if(isset($this->trailerFile)) $trailerfile = $this->trailerFile;
        else $trailerfile = null;

        if(isset($this->videofile)) $videofile = $this->videofile;
        else if(isset($this->videoFile)) $videofile = $this->videoFile;
        else $videofile = null;   

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'year_launched' => $year_launched,
            'duration' => $this->duration,
            'rating' => $this->rating,
            'opened' => $this->opened,
            'categories_id' => $categories_id,
            'genres_id' => $genres_id,
            'cast_members_id' => $cast_members_id,
            'thumbfile' => $thumbfile,
            'thumbhalf' => $thumbhalf,
            'bannerfile' => $bannerfile,
            'trailerfile' => $trailerfile,
            'videofile' => $videofile,
            'created_at' => Carbon::make($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::make($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}