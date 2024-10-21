<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>マイページ</title>
     <!--css-->
    <link href="{{ asset('/css/mypage.css') }}" rel="stylesheet" />
</head>

<body>
    <!--ヘッダー-->
    <div class = "header">
        <a href = "/">トップ</a>
        @guest
          <a href = "/register">新規登録</a>
          <a href = "/posts/mypage">ログイン</a>
        @endguest
        
        @auth  
          <a href = "/posts/mypage">マイページ</a>
        @endauth
        <a href = "/posts/postsAll">投稿表示</a>
        <a href="/posts/create">投稿</a>
        <a href="/maps/place">地点検索</a>
        <a href="/maps/search">ピンポイント検索</a>
        <a href="/maps/severalRoute">複数地点検索</a>
        <a href="/maps/navi">公共交通機関</a>
    </div>
    
    <!--お気に入り地点の表示フォーム-->
    <div class = "favoritePlace">
        <h4>お気に入り地点</h4>
        @foreach($favoritePlaces as $favoritePlace)
            <a class = "favoritePoint" href = "/posts/placeComment/{{$favoritePlace->id}}">{{$favoritePlace->name}}</a><br><br>
        @endforeach
        <a href="/maps/favoriteplaceEdit" class = "more">さらに表示</a>
    </div>
    
     <!--お気に入りルートの表示フォーム-->
    <div class = "route">
        <h4>登録ルート</h4>
        @foreach($routes as $route)
            <a class = "routename" href = "/posts/routeDetail/{{$route->id}}">
                @if(!empty($route->start))    
                    {{ \Carbon\Carbon::parse($route->start)->format('m月d日 H:i') }}発<br> 
                @endif
                {{$route->title}}</a><br><br>
        @endforeach
        <a href="/maps/routeEdit" class = "more">さらに表示</a>
    </div>
    
    <!-- 投稿の表示フォーム -->
    <div class="post">
        <h4>投稿</h4>
        @foreach($posts as $post) 
        <a href="/posts/{{$post->id}}" class = "title">{{$post->title}}</a>
        <p class = "place">{{$post->temple}}</p>
        <img src="{{$post->image}}" alt="写真" class = "image">
        <br>
        @endforeach
    </div>
    <!--ペジネーションリンク-->
   <div class = 'postsPaginate'>{{$posts->links()}}</div>
    
</body>