<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            //user_idが同じ時に重複を避ける
            'favoritePlace.name' => [
                'required',
                Rule::unique('favorite_places', 'name')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'favoritePlace.place_id' => [
                'required',
                Rule::unique('favorite_places', 'place_id')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'favoritePlace.latitude' => [
                'required',
                Rule::unique('favorite_places', 'latitude')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'favoritePlace.longitude' => [
                'required',
                Rule::unique('favorite_places', 'longitude')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
        ];
    }
}
