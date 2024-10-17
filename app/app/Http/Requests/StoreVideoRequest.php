<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
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
                'min:1',
                'integer',
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
            ],
            'thumbhalf' => [
                'nullable',
            ],
            'bannerfile' => [
                'nullable',
            ],
            'trailerfile' => [
                'nullable',
            ],
            'videofile' => [
                'nullable',
            ],
        ];
    }
}
