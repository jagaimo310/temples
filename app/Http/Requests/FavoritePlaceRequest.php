<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FavoritePlaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'favoritePlace.name' => 'required|unique:favorite_places,name',
            'favoritePlace.place_id' => 'required|unique:favorite_places,place_id',
            'favoritePlace.latitude' => 'required|unique:favorite_places,latitude',
            'favoritePlace.longitude' => 'required|unique:favorite_places,longitude',
           
        ];
    }
}
