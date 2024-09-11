<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Category;
use App\Models\Place;
use App\Models\User;
use App\Models\FavoritePlace;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\FavoritePlaceRequest;
use App\Http\Requests\PostUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Cloudinary;

class PostController extends Controller
{
    //
    public function test(Request $request){
        //検索機能
        $keyword=$request["blogSearch"];
        
        $post = Post::query();
        $posts = "";
        $message = "";
        //検索された場合
        if(!empty($keyword)) {
            //postテーブルの条件
            $post->where('temple', 'LIKE', "%{$keyword}%")
                ->orWhere('comment', 'LIKE', "%{$keyword}%");
            //placeテーブルの条件
            $post->orWhereHas('place', function($place) use ($keyword) {
                $place->where('prefecture', 'LIKE', "%{$keyword}%")
                     ->orWhere('area', 'LIKE', "%{$keyword}%");
            });
            //結果を取得
            $posts = $post -> get();
            
            //結果が見つからなかった場合
            if($posts->isEmpty()){
                $message = "該当する投稿は見つかりませんでした。";
            }
            
        }
        
        return view('maps.test',compact('posts', 'keyword','message'));
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
    
    public function myPage(User $user){
        //postをuser_idで絞り込む
        $post = $user -> posts;
        //favoritePlaceをuser_idで絞り込む
        $favoritePlaces = $user -> favorite_places;
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
        return redirect('/posts/mypage/'.Auth::id());
    }
    
    //お気に入り地点編集用ページ
    public function favoriteplaceEdit(User $user){
        $favoriteplaces = $user -> favorite_places;
        return view('maps.favoritePlaceEdit') -> with(['favoritePlaces' => $favoriteplaces ]);
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
    public function delete(Post $post){
        $post->delete();
        return redirect('/');
    }
    
    //お気に入り地点削除
    public function deleteFavoritePlace(Request $request){
        //送られてきたidを配列に追加
        $favoritePlaces = $request['favoritePlace_array'];
        //whereInで検索して各自削除処理
        if(!empty($favoritePlaces)){
            FavoritePlace::whereIn('id', $favoritePlaces)->delete();
            return redirect('/posts/mypage/'.Auth::id());
        }else{
            return redirect('/maps/'.Auth::id());
        }
    }
}

