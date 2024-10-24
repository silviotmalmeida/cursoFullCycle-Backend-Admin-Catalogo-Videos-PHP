<?php

namespace App\Http\Requests;

use Core\Domain\Enum\Rating;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // definindo as validações dos atributos
            'title' => [
                'required',
                'min:3',
                'max:255',
            ],
            'description' => [
                'required',
                'min:3',
                'max:255',
            ],
            'year_launched' => [
                'required',
                'date_format:Y',
            ],
            'duration' => [
                'required',
                'min:1',
                'integer',
            ],
            'opened' => [
                'nullable',
                'boolean',
            ],
            'rating' => [
                'required',
                new Enum(Rating::class)
            ],             
            'categories_id' => [
                'nullable',
                'array',
                'exists:categories,id,deleted_at,NULL',
            ],
            'genres_id' => [
                'nullable',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ],
            'cast_members_id' => [
                'nullable',
                'array',
                'exists:cast_members,id,deleted_at,NULL',
            ],
            'thumbfile' => [
                'nullable',
                'image'
            ],
            'thumbhalf' => [
                'nullable',
                'image'
            ],
            'bannerfile' => [
                'nullable',
                'image'
            ],
            'trailerfile' => [
                'nullable',
                'mimetypes:video/mp4'
            ],
            'videofile' => [
                'nullable',
                'mimetypes:video/mp4'
            ],
        ];
    }
}
