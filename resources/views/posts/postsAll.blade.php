<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!--css-->
    <link href="{{ asset('/css/postAll.css') }}" rel="stylesheet" />
    <title>投稿表示</title>
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
    
    <div class = "showBlog">
        <div class = "serchForm">
            <form>
                <input type = "text" class = "inputForm"  name = "serch" value = {{$keyword}}>
                <input type = "submit" class = "button" value = "検索">
            </form>
        </div>
        @foreach($posts as $post)
            <a href="/posts/{{$post->id}}" class = "title">{{$post->title}}</a>
            <p class = "name">{{$post->temple}}</p>
            <img src="{{$post->image}}" alt="写真" class = "photo">
            <br>
        @endForeach
    
        <div class = "message">
            @if(isset($message))
                <p>{{$message}}</p>
            @endif
        </div>
    </div>
</body>
</html>