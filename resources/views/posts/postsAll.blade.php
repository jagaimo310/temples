<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>複数ルート検索</title>
</head>
<body>
    <!--ヘッダー-->
    <div class = "header">
        <a href = "/">トップ</a>
        <a href = "/register">新規登録</a>
        <a href = "/posts/mypage">ログイン・マイページ</a>
        <a href = "/posts/postsAll">投稿表示</a>
        <a href="/posts/create">投稿</a>
        <a href="/maps/place">地点検索</a>
        <a href="/maps/search">ピンポイント検索</a>
        <a href="/maps/severalRoute">複数地点検索</a>
        <a href="/maps/navi">公共交通機関</a>
    </div>
    
    <div class = "serchForm">
        <form>
            <input type = "text"  name = "serch" value = {{$keyword}}>
            <input type = "submit" value = "検索">
        </form>
    </div>
    
    <div class = "showBlog">
        @foreach($posts as $post)
            <a href="/posts/{{$post->id}}">{{$post->title}}</a>
            <p>{{$post->temple}}</p>
            <img src="{{$post->image}}" alt="写真">
            <br>
        @endForeach
    </div>
    <div class = "message">
        @if(isset($message))
            <p>{{$message}}</p>
        @endif
    </div>
</body>
</html>