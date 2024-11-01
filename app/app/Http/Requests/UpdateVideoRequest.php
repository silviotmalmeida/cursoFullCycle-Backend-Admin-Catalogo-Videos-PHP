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
                'min:3',
                'max:255',
            ],
            'description' => [
                'min:3',
                'max:255',
            ],
            'year_launched' => [
                'date_format:Y',
            ],
            'duration' => [
                'min:1',
                'integer',
            ],
            'opened' => [
                'nullable',
                'boolean',
            ],
            'rating' => [
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
                // 'array',
                // 'image'
            ],
            'thumbhalf' => [
                'nullable',
                // 'array',
                // 'image'
            ],
            'bannerfile' => [
                'nullable',
                // 'array',
                // 'image'
            ],
            'trailerfile' => [
                'nullable',
                // 'array',
                // 'mimetypes:video/mp4'
            ],
            'videofile' => [
                'nullable',
                // 'array',
                // 'mimetypes:video/mp4'
            ],
        ];
    }
}
