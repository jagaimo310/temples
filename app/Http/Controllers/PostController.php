<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Category;
use App\Models\Place;
use App\Models\FavoritePlace;
use App\Http\Requests\PostRequest;
use App\Http\Requests\FavoritePlaceRequest;
use App\Http\Requests\PostUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Cloudinary;

class PostController extends Controller
{
    //
    public function test(){
        return view('maps.test');
    }
    
     public function navi(){
        return view('maps.navi');
    }
    

    public function detail(){
        return view('maps.detail');
    }
    

    public function map(Post $post){
        return view('maps.map')->with(['posts'=>$post->getBylimit()]);
    }
    
    public function create(Category $category,  Place $place){
        return view('posts.create')->with(['categories'=>$category->get()]);
        
    }
    
    public function posts(){
        return view('maps.map');
    }
    
    public function show(Post $post){
         return view('posts.show')->with(['post'=>$post]);
    }
    
    public function myPage(Post $post, FavoritePlace $favoritePlaces){
        //postをuser_idで絞り込む
        $post = DB::select('SELECT * FROM posts WHERE user_id = ?', [Auth::id()]);
        //favoritePlaceをuser_idで絞り込む
        $favoritePlaces = DB::select('SELECT * FROM favorite_places WHERE user_id = ?', [Auth::id()]);
        return view('posts.mypage')->with(['posts'=>$post,'favoritePlaces'=>$favoritePlaces]);
    }
    
    //投稿保存
    public function store(PostRequest $request, Post $post, Place $place){
        //先に場所の処理
        $input_place = $request['post_places'];
        $place->prefecture = $input_place['prefecture'];
        $place->area = $input_place['city'];
        $place->save();
        //写真以外をDBの項目に当てはめる
        $input_post=$request['post'];
        $post->title = $input_post['title'];
        $post->temple = $input_post['temple'];
        $post->comment = $input_post['comment'];
        $post->user_id = Auth::id();
        //既に保存処理した$placeのidを使用
        $post->place_id = $place->id;
        
        //cloudinaryへ画像を送信し、画像のURLを$image_urlに代入している
        $image_url = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
          //画像のURLを画面に表示
        $post->image = $image_url;
        $post->save();
        //カテゴリーの処理
        $input_categories=$request->categories_array;
        $post->categories()->attach($input_categories);
        //リダイレクト
        return redirect('/posts/' . $post->id); 
    }
    
    //お気に入り地点保存
    public function favoritePlace(FavoritePlaceRequest $request, FavoritePlace $favoritePlace){
        $input_favoritePlace = $request['favoritePlace'];
        $favoritePlace->user_id = Auth::id();
        $favoritePlace->fill($input_favoritePlace)->save();
        return redirect('/posts/mypage');
    }
    
    
    //編集機能表示用
    public function edit(Post $post, Category $category)
    {
        return view('posts.edit')->with(['post' => $post, 'categories'=>$category->get()]);
    }
    
    //編集処理用
    public function update(PostUpdate $request, Post $post)
    {
        //場所の処理 updateの場合$postはすでに入っている値を使うのでそれに結びついたplaceテーブルの行を取り出す
        $place = $post ->place;
        //入力が合った場合のみ更新処理を行う
        if (!empty($request->post_places['prefecture']) && !empty($request->post_places['city'])) {
            $input_place = $request['post_places'];
            $place->prefecture = $input_place['prefecture'];
            $place->area = $input_place['city'];
            $place->save();
        }
        //写真以外をDBの項目に当てはめる
        $input_post=$request['post'];
        $post->title = $input_post['title'];
        $post->temple = $input_post['temple'];
        $post->comment = $input_post['comment'];
        
        //入力が合った場合のみ更新処理を行う
        if ($request->hasFile('image')) {
            //cloudinaryへ画像を送信し、画像のURLを$image_urlに代入している
            $image_url = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
              //画像のURLを画面に表示
            $post->image = $image_url;
        }
        //更新実行
        $post->save();
        
        //カテゴリーの処理
        //入力が合った場合のみ更新処理を行う
        if ($request->has('categories_array')) {
            $input_categories=$request->categories_array;
            $post->categories()->sync($input_categories);
        }
        //リダイレクト
        return redirect('/posts/' . $post->id); 
    }
    
    
    //投稿削除
    public function delete(Post $post)
    {
        $post->delete();
        return redirect('/');
    }
}
