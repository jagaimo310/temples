<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'post.title' => 'required|string|max:100',
            'post.temple'=>'required|string|max:100',
            'post.comment' => 'required|string|max:4000',
            //cloudinaliyの設定の制限（1700kB）をかける
            'image'=>'required|file|image|mimes:jpeg,png,png|max:1700',
            'post_places.city' =>'required',
            'post_places.prefecture' =>'required'
        ];
    }
}
