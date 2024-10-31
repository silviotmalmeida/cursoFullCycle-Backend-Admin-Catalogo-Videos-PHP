<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

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
        // ajustando o year_launched:
        $year_launched = isset($this->year_launched) ? $this->year_launched : $this->yearLaunched;

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
        // ajustando os genres:
        $genres_id = [];
        if (isset($this->genres_id)) {
            $genres_id = $this->genres_id;
        } else if (isset($this->genresId)) {
            $genres_id = $this->genresId;
        }
        else if (isset($this->genres)) {
            $genres_id = Arr::pluck($this->genres, 'id');
        }
        // ajustando os castMembers:
        $cast_members_id = [];
        if (isset($this->cast_members_id)) {
            $cast_members_id = $this->cast_members_id;
        } else if (isset($this->castMembersId)) {
            $cast_members_id = $this->castMembersId;
        }
        else if (isset($this->cast_members)) {
            $cast_members_id = Arr::pluck($this->cast_members, 'id');
        }
        // ajustando os thumbfile:
        $thumbfile = null;
        if (isset($this->thumbfile)) {
            $thumbfile = $this->thumbfile;
        } else if (isset($this->thumbFile)) {
            $thumbfile = $this->thumbFile;
        }
        else if (isset($this->thumb)) {
            $thumbfile = $this->thumb['path'];
        }
        // ajustando os thumbhalf:
        $thumbhalf = null;
        if (isset($this->thumbhalf)) {
            $thumbhalf = $this->thumbhalf;
        } else if (isset($this->thumbHalf)) {
            $thumbhalf = $this->thumbHalf;
        }
        else if (isset($this->thumb_half)) {
            $thumbhalf = $this->thumb_half['path'];
        }
        // ajustando os bannerfile:
        $bannerfile = null;
        if (isset($this->bannerfile)) {
            $bannerfile = $this->bannerfile;
        } else if (isset($this->bannerFile)) {
            $bannerfile = $this->bannerFile;
        }
        else if (isset($this->banner)) {
            $bannerfile = $this->banner['path'];
        }
        // ajustando os trailerfile:
        $trailerfile = null;
        if (isset($this->trailerfile)) {
            $trailerfile = $this->trailerfile;
        } else if (isset($this->trailerFile)) {
            $trailerfile = $this->trailerFile;
        }
        else if (isset($this->trailer)) {
            $trailerfile = $this->trailer['file_path'];
        }
        // ajustando os videofile:
        $videofile = null;
        if (isset($this->videofile)) {
            $videofile = $this->videofile;
        } else if (isset($this->videoFile)) {
            $videofile = $this->videoFile;
        }
        else if (isset($this->video)) {
            $videofile = $this->video['file_path'];
        }

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
