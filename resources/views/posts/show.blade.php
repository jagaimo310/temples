<!DOCTYPE HTML>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Posts</title>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places" async defer></script>
    </head>
 
 
    <body>
        <h1 class="title">{{ $post->title }}</h1>
        <div class="content">
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
             <!-- 写真表示 -->
            <div class="content_image">
                <img src="{{ $post->image }}" alt="写真">    
            </div>
        </div>
        <!--フッター-->
        <div class="footer">
            @if (Auth::id() === $post->user_id)
                <a href='/posts/{{$post->id}}/edit' class="edit">編集</a>
            
                <form action='/posts/{{$post->id}}' id="form_{{ $post->id }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="deletePost({{ $post->id }})">削除</button>
                </form>
            @endif
            <a href="javascript:history.back()">戻る</a>
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
                    window.location.href = `/maps/detail?lat=${lat}&lng=${lng}&id=${id}&name=${name}`; 
                }
                else {
                  alert(`${address}の位置情報が取得できませんでした。`);
                }
              });
            }
        </script>
    </body>
</html>