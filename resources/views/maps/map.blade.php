<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>Map</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('/build/assets/map-CN376zLE.css') }}">
</head>
    

<body>
     <div class=header>
        <a href="/posts/create">投稿</a>
        <a href="/register">新規登録</a>
        <a href = "/posts/mypage">ログイン・マイページ</a>
        <a href="/maps/place">地点検索</a>
        <a href="/maps/search">ピンポイント検索</a>
        <a href="/maps/severalRoute">複数地点検索</a>
    </div>

    <input
        id="pac-input"
        class="controls"
        type="text"
        placeholder="Search Box"
    />
    <div id="map" style="width:800px; height:800px; margin: auto">
        <script src="https://maps.googleapis.com/maps/api/js?language=ja&region=JP&key={{ config("services.google-map.apikey") }}&callback=initAutocomplete&libraries=places&v=weekly"defer></script>
    </div>
    
    <!-- 投稿の表示フォーム -->
    <div class="post">
        @foreach($posts as $post) 
        <a href="/posts/{{$post->id}}">{{$post->title}}</a>
        <p>{{$post->temple}}</p>
        <img src="{{$post->image}}" alt="写真">
        <br>
        @endforeach
    </div>
    
    <div class='paginate'>
        {{ $posts->links()}}
    </div>
   
    
</body>
    