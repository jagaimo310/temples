<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Category;
use App\Models\Place;
use App\Models\User;
use App\Models\FavoritePlace;
use App\Models\Route; 
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\FavoritePlaceRequest;
use App\Http\Requests\PostUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Cloudinary;
use Socialite; 
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;


class PostController extends Controller
{
    //
    public function top(){
        return view('maps.top');
    }
    
    
    public function postsAll(Post $post,Request $request){
        $category = "";
        $keyword = $request["serch"];
        $category = $request["category"];
        //検索時、$keyword、$category両方が入力されていたとき
        if(!empty ($keyword && !empty ($category))){
            $message ="";
            $post = Post::query();
            $post->where(function($main) use ($keyword,$category) {
                $main->where('temple', 'LIKE', "%{$keyword}%")
                     ->orWhere('comment', 'LIKE', "%{$keyword}%")
                     ->orWhereHas('place', function($place) use ($keyword) {
                // placeテーブルの条件をORで結びつけ
                        $place->where('prefecture', 'LIKE', "%{$keyword}%")
                            ->orWhere('area', 'LIKE', "%{$keyword}%");
                });
            });
            // categoriesテーブルの条件をANDで適用
            $post -> whereHas('categories', function($DBcategory) use ($category) {
                $DBcategory->where('name', 'LIKE', "%{$category}%");
            });
            $serchPosts = $post -> orderBy('updated_at', 'DESC') -> paginate(5);
            //結果が見つからなかった場合
            if($serchPosts->isEmpty()){
                $message = "該当する投稿は見つかりませんでした。";
            }
            return view('posts.postsAll')->with(['posts'=>$serchPosts,'keyword'=>$keyword,'category'=>$category,'message'=>$message]);
        //$categoryのみが入力されていたとき
        }elseif(!empty ($category) && empty ($keyword)){
            $message ="";
            $keyword = "";
            $postCategory = Post::query();
            $postCategory -> orWhereHas('categories', function($DBcategory) use ($category) {
                $DBcategory -> where('name', 'LIKE', "%{$category}%");
            });
            $serchPosts = $postCategory -> orderBy('updated_at', 'DESC') -> paginate(5);
            //結果が見つからなかった場合
            if($serchPosts->isEmpty()){
                $message = "該当する投稿は見つかりませんでした。";
            }
            return view('posts.postsAll')->with(['posts'=>$serchPosts,'keyword'=>$keyword,'category'=>$category,'message'=>$message]);
            
        //$keywordのみが入力されていたとき   
        }elseif(!empty ($keyword) && empty ($category)){
            $message ="";
            $category = "";
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
            return view('posts.postsAll')->with(['posts'=>$serchPosts,'keyword'=>$keyword,'category'=>$category,'message'=>$message]);
        }
        
        
        
        return view('posts.postsAll')->with(['posts'=>$post->getPaginateByLimit(5),'keyword'=>$keyword,'category'=>$category]);
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
        
        //リダイレクト
        return redirect('/maps/search?placeName='.urlencode($name).'&placeId='.$request->placeId)->with(['posts'=>$posts,'message'=>$message ]); 
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
                
        if (Auth::check()) { 
            $user = Auth::user();
            $favoritePlaces = $user -> favorite_places() -> orderBy('prefecture', 'asc') ->get();
            return view('maps.detail')->with(['posts'=>$posts,'message'=>$message,'favoritePlaces'=>$favoritePlaces]);
        }else{
            return view('maps.detail')->with(['posts'=>$posts,'message'=>$message]); 
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
        $favoritePlaces = $user->favorite_places() -> orderBy('prefecture', 'ASC')-> paginate(10);
        $routes = $user->routes() -> orderBy('updated_at', 'DESC') ->paginate(10);;
        return view('posts.mypage')->with(['posts' => $posts,'favoritePlaces' => $favoritePlaces,'routes' => $routes]);
    }
    
    public function placeComment(FavoritePlace $favoritePlace){
        return view('posts.placeComment')->with(['favoritePlace'=>$favoritePlace]);
    }
    
    //ルート情報詳細
    public function routeDetail(Route $route){
        return view('posts.routeDetail')->with(['route'=>$route]);
    }
    
    //ルート情報全表示
    public function routeEdit(){
        $user = Auth::user();
        $routes = $user -> routes()-> orderBy('updated_at', 'DESC')->get();
        return view('maps.routeEdit') -> with(['routes' => $routes ]);
    }
    
    //お気に入り地点共有用ページ
    public function placeShare(FavoritePlace $favoritePlace){
        return view('posts.placeShare') -> with(['favoritePlace'=>$favoritePlace ]);
    }
    
    //ルート共有用ページ
    public function routeShare(Route $route){
        return view('posts.routeShare') -> with(['route' => $route ]);
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
    
    //ルート保存
    public function saveRoute(Request $request, Route $route){
        // リクエストからデータを取得
        $input_title = $request->input('title');
        $input_start = $request->input('start');
        $input_end = $request->input('end');
        //データがjson形式になっているのでデコード
        $decode = json_decode($request->input('content'), true);
        $input_content = $decode['content'];
        // contentを保存
        $route->user_id = Auth::id();
        $route->title = $input_title;
        $route->start = $input_start;
        $route->end = $input_end;
        $route->content = $input_content;
        $route->save();
        return redirect('/posts/mypage');
    }

    //お気に入り地点削除
    public function deleteRoute(Request $request){
        //送られてきたidを配列に追加
        $routes = $request['route_array'];
        //whereInで検索して各自削除処理
        if(!empty($routes)){
            Route::whereIn('id', $routes)->delete();
            return redirect('/posts/mypage');
        }else{
            return redirect('/maps/routeEdit');
        }
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
    
    //お気に入り地点コメント編集
    public function favoritePlaceUpdate(Request $request, favoritePlace $favoritePlace){
        $favoritePlace->comment = $request['comment'];
        $favoritePlace->save();
        return redirect('/posts/placeComment/'. $favoritePlace -> id);
    }
    
    public function routeUpdate(Request $request, Route $route){
        $route->memo = $request['memo'];
        $route->save();
        return redirect('/posts/routeDetail/'. $route -> id);
    }
    
    //googleカレンダー用
    public function google(Route $route){
        // routeをセッションに保存
        Session::put('route', $route);
        return redirect('/login/google');
    }
    
    public function redirectToGoogle(){
         return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar.events'])
            ->redirect();
    }
    
    public function handleGoogleCallback(){
        // セッションからrouteを取得
        $route = Session::get('route');
        //カレンダーの処理
        $user = Socialite::driver('google')->stateless()->user();
        $token = $user->token;
        
        // 取得したアクセストークンを使ってGoogle Calendar APIにアクセス
        $client = new GoogleClient();
        $client->setAccessToken($token);
        
        $calendarService = new Calendar($client);

        // 予定の追加
        $event = new \Google\Service\Calendar\Event([
            'summary' => $route -> title,
            // 8192字まで
            'description' => $route -> memo .url('/posts/routeShare/'.$route->id),
            'start' => [
                'dateTime' => $route -> start,
                'timeZone' => 'Asia/Tokyo',
            ],
            'end' => [
                'dateTime' => $route -> end,
                'timeZone' => 'Asia/Tokyo',
            ],
        ]);

        $calendarId = 'primary'; // メインのカレンダーID（"primary"で指定できます）
        $calendarService->events->insert($calendarId, $event);
        return redirect('/posts/routeDetail/'.$route -> id)->with(['success' => 'success',]);
    }
    
}

