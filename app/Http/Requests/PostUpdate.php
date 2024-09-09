<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdate extends FormRequest
{
    
    
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
            'post.temple' => 'required|string|max:100',
            'post.comment' => 'required|string|max:4000',
            
            // 画像ファイルは更新時にのみ必須
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg|max:1700',
    
            // 都道府県と市区町村は更新時にのみ必須
            'post_places.city' => 'nullable|string',
            'post_places.prefecture' => 'nullable|string'
        ];
    }
}
