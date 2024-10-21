<!DOCTYPE HTML>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>投稿詳細</title>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places" defer></script>
        <!--css-->
        <link href="{{ asset('/css/show.css') }}" rel="stylesheet" />
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
        
        
        <div class="content">
            <h1 class="title">{{ $post->title }}</h1>
            <div class = "contentOutPhoto">
                 <!-- 寺院名表示 -->
                <div class="content_temple">
                    <h3>寺院名</h3>
                    <a href="javascript:void(0);" onclick="getPlace('{{ $post->temple }}');">{{ $post->temple }}</a>    
                </div>
                 <!-- 都道府県表示 -->
                <div class="content_place">
                    <h3>都道府県</h3>
                    <p>{{ $post->place->prefecture }}</p>  
                    <p>{{ $post->place->area }}</p>
                </div>
                 <!-- カテゴリー表示 -->
                <div class="content_post">
                    <h3>カテゴリー</h3>
                    <p>
                     @foreach($post->categories as $category)   
                        {{$category->name}}
                    @endforeach
                    </p>
                    
                 <!-- コメント表示 -->
                <div class="content_comment">
                    <h3>コメント</h3>
                    <p>{{ $post->comment }}</p> 
                </div>
            </div>
        </div>    
        
             <!-- 写真表示 -->
            <div class="content_image">
                <img src="{{ $post->image }}" alt="写真">    
            </div>
        
        <!--フッター-->
        <div class="footer">
            @if (Auth::id() === $post->user_id)
                <a href='/posts/{{$post->id}}/edit' class="edit" class = "button edit">編集</a>
            
                <form action='/posts/{{$post->id}}' id="form_{{ $post->id }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="deletePost({{ $post->id }})" class = "button delete">削除</button>
                </form>
            @endif
            <a href="/posts/postsAll" class = "button return">戻る</a>
        </div>
    </div>    
        
    <script>
            function deletePost(id) {
                'use strict'
        
                if (confirm('削除すると復元できません。\n本当に削除しますか？')) {
                    document.getElementById(`form_${id}`).submit();
                }
            }
            
            //寺院名がクリックされた場合に詳細画面に映る処理をする
            function getPlace(address){
                let name = "{{ $post->temple }}";
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                address: address
              },
              function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    let location = results[0].geometry.location;
                    let lat = location.lat();
                    let lng = location.lng();
                    let id = results[0].place_id;;
                    // 指定したURLに遷移
                    window.location.href = `/maps/{{ $post->temple }}?lat=${lat}&lng=${lng}&id=${id}&name=${name}`; 
                }
                else {
                  alert(`${address}の位置情報が取得できませんでした。`);
                }
              });
            }
        </script>
    </body>
</html>