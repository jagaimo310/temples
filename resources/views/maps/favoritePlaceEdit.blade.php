<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>お気に入り地点</title>
    <!--css-->
    <link href="{{ asset('/css/favoritePlace.css') }}" rel="stylesheet" />
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
    
    <!--お気に入り地点の削除-->
    <div class = "favoritePlace">
        <form action='/maps/deleteFavoritePlace'  method="POST" class = form>
            @csrf
            @method('DELETE')
            @foreach($favoritePlaces as $favoritePlace)
            
                <label>
                    {{-- valueを'$subjectのid'に、nameを'配列名[]'に --}}
                    <input type="checkbox" value="{{ $favoritePlace->id }}" name = 'favoritePlace_array[]' >
                        <a href = "/posts/placeComment/{{$favoritePlace->id}}">{{$favoritePlace->name}}</a><br>
                        <p>{!!$favoritePlace->comment!!}</p>
                    </input>
                </label>
                
            @endforeach 
            
            <div class="button-group">
                <input type="submit" value="削除" class="delete">
            </div>
        </form>
        
        <div class="button-group">
            <a href="/posts/mypage" class="return">戻る</a>
        </div>
    </div>
</body>