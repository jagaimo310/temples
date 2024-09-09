<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
</head>

<body>
    <!--お気に入り地点の表示フォーム-->
    <div class = "favoritePlace">
        @foreach($favoritePlaces as $favoritePlace)
            <a href = "/maps/detail?lat={{$favoritePlace->latitude}}&lng={{$favoritePlace->longitude}}&id={{$favoritePlace->place_id}}&name={{$favoritePlace->name}}">{{$favoritePlace->name}}</a>
        @endforeach
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
   
    
</body>