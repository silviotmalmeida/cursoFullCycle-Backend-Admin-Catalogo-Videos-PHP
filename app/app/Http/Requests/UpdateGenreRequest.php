<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGenreRequest extends FormRequest
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
            'name' => [
                'min:3',
                'max:255',
            ],
            'is_active' => [
                'boolean',
            ],
            'categories_id' => [
                'array',
                'exists:categories,id,deleted_at,NULL',
            ]
        ];
    }
}
