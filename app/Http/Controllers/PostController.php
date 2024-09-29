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
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Str;
use Cloudinary;

class PostController extends Controller
{
    //
    public function top(){
        return view('maps.top');
    }
    
    
    public function postsAll(Post $post,Request $request){
        $keyword = $request["serch"];
        
        if(!empty ($keyword)){
            $message ="";
            $postKeyword = Post::query();
            $postKeyword -> where('temple', 'LIKE', "%{$keyword}%")
                  ->orWhere('comment', 'LIKE', "%{$keyword}%");
            //placeテーブルの条件
            $postKeyword ->orWhereHas('place', function($place) use ($keyword) {
                $place->where('prefecture', 'LIKE', "%{$keyword}%")
                      ->orWhere('area', 'LIKE', "%{$keyword}%");
            });
            $serchPosts = $postKeyword -> orderBy('updated_at', 'DESC') -> paginate(5);
            //結果が見つからなかった場合
            if($serchPosts->isEmpty()){
                $message = "該当する投稿は見つかりませんでした。";
            }
            return view('posts.postsAll')->with(['posts'=>$serchPosts,'keyword'=>$keyword,'message'=>$message]);
        }
        
        return view('posts.postsAll')->with(['posts'=>$post->getPaginateByLimit(5),'keyword'=>$keyword]);
    }
    
    //map.search
    public function search(){
        return view('maps.search');
    }
    
    //searchで詳細表示する際の表示
    public function retrieval(Request $request){
        //投稿の検索
        $post = Post::query();
        $message = "";
        $name = "";
        if(!empty($request["placeRealName"])){
            $name = $request["placeRealName"];
        }else{
            $name = $request["placeName"];
        }
        
        $post->where('temple', 'LIKE', "%{$name}%")
            ->orWhere('comment', 'LIKE', "%{$name}%");
        //placeテーブルの条件
        $post->orWhereHas('place', function($place) use ($name) {
            $place->where('prefecture', 'LIKE', "%{$name}%")
                 ->orWhere('area', 'LIKE', "%{$name}%");
        });
        //結果を取得
        $posts = $post ->orderBy('updated_at', 'DESC') -> paginate(10);
        
        //結果が見つからなかった場合
        if($posts->isEmpty()){
            $message = "該当する投稿は見つかりませんでした。";
        }
        
        //gemini api
        $question = $request->placeName. "について500字以内で教えてください。";
        $answer = Str::markdown(Gemini::geminiPro()->generateContent($question)->text());
        //リダイレクト
        return redirect('/maps/search?placeName='.urlencode($request->placeName).'&placeId='.$request->placeId)->with(['posts'=>$posts,'message'=>$message,'answer'=>$answer ]); 
    }
    
     public function navi(){
         if (Auth::check()) { 
            $user = Auth::user();
            $favoritePlaces = $user -> favorite_places() -> orderBy('prefecture', 'asc') ->get();
            return view('maps.navi')->with(['favoritePlaces'=>$favoritePlaces ]);
         }else{
            return view('maps.navi'); 
         }
    }
    

    public function detail($name,Request $request){
        $post = Post::query();
        $message = "";
        
        $post->where('temple', 'LIKE', "%{$name}%")
            ->orWhere('comment', 'LIKE', "%{$name}%");
        //placeテーブルの条件
        $post->orWhereHas('place', function($place) use ($name) {
            $place->where('prefecture', 'LIKE', "%{$name}%")
                 ->orWhere('area', 'LIKE', "%{$name}%");
        });
        //結果を取得
        $posts = $post ->orderBy('updated_at', 'DESC') -> paginate(10);
        
        //結果が見つからなかった場合
        if($posts->isEmpty()){
            $message = "該当する投稿は見つかりませんでした。";
        }
        
        //gemini api
        $question = $name . "について500字以内で教えてください。";
        $answer = Str::markdown(Gemini::geminiPro()->generateContent($question)->text());
                

        if (Auth::check()) { 
            $user = Auth::user();
            $favoritePlaces = $user -> favorite_places() -> orderBy('prefecture', 'asc') ->get();
            return view('maps.detail')->with(['posts'=>$posts,'message'=>$message,'favoritePlaces'=>$favoritePlaces,'answer'=>$answer]);
        }else{
            return view('maps.detail')->with(['posts'=>$posts,'message'=>$message,'answer'=>$answer]); 
         }
    }
    
    public function place(){
        if (Auth::check()) {
            $user = Auth::user();
            $favoritePlaces = $user -> favorite_places() -> orderBy('prefecture', 'asc') ->get();
            return view('maps.place')->with(['favoritePlaces'=>$favoritePlaces]);
        }else{
            return view('maps.place');
        }
    }
    
    public function severalRoute(){
        if (Auth::check()) {
            $user = Auth::user();
            $favoritePlaces = $user -> favorite_places() -> orderBy('prefecture', 'asc') ->get();
            return view('maps.severalRoute')->with(['favoritePlaces'=>$favoritePlaces]);
        }else{
            return view('maps.severalRoute');
        }
    }

    
    public function create(){
        $category = Category::all();
        return view('posts.create')->with(['categories'=>$category]);
        
    }
    
    
    public function show(Post $post){
         return view('posts.show')->with(['post'=>$post]);
    }
    
    public function myPage(){
    // 現在のユーザーを取得
    $user = Auth::user();

    // リレーションされたデータベースを取得し並び替えた上でpaginateを適用してを取得
    $posts = $user -> posts() -> orderBy('updated_at', 'DESC')->paginate(5);
    $favoritePlaces = $user->favorite_places() -> orderBy('prefecture', 'asc');
    
    return view('posts.mypage')->with(['posts' => $posts,'favoritePlaces' => $favoritePlaces -> paginate(10)]);
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
    
    //お気に入り地点編集用ページ
    public function favoriteplaceEdit(){
        $user = Auth::user();
        $favoriteplaces = $user -> favorite_places()-> orderBy('prefecture', 'asc')->get();
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
            return redirect('/posts/mypage');
        }else{
            return redirect('/maps/'.Auth::id());
        }
    }
}

