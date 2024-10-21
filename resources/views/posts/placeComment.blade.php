<!DOCTYPE html>
<html lang="ja">
 
 <head>
    <meta charset="utf-8">
   <title>お気に入り地点詳細</title>
    <!--css-->
    <link href="{{ asset('/css/placeComment.css') }}" rel="stylesheet" />
    <!--trix-->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"></script>
    <style>
        trix-toolbar [data-trix-button-group="file-tools"] {
        display: none; 
    }
    </style>
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
        
    <a class = "title" href = "/maps/{{$favoritePlace->name}}?lat={{$favoritePlace->latitude}}&lng={{$favoritePlace->longitude}}&id={{$favoritePlace->place_id}}&name={{$favoritePlace->name}}" class = "favolite">{{$favoritePlace->name}}</a><br>
    <p class = "prefecture">{{$favoritePlace->prefecture}} {{$favoritePlace->area}}</p>
    <div id = "mapArea" class = "mapArea" style = "width:80%; height:400px;"></div><br>
    <div id = "show" class = "show">
        @if(!empty($favoritePlace->comment))
            <div class = "memoForm">
                <p>{!!$favoritePlace->comment!!}</p>
            </div>
        @endif
        <button onclick = "editComment()" class = "button">コメント追加・編集</button>
    </div>
    
   <div id = "comment"></div> 
   <div id = "share"  class = "share">
       <a  class = "line" href="https://social-plugins.line.me/lineit/share?url={{url('/posts/placeShare/'.$favoritePlace->id)}}" target="_blank">Lineで共有</a>
       <a  class = "mail" href="mailto:?subject={{$favoritePlace->name}}&body=旅行のおすすめ地点{{url('/posts/placeShare/'.$favoritePlace->id)}}">Mailで共有</a>
    </div>
    <div id = "print"  class = "share">
       <button class = "print"  onclick = "window.print()">印刷</button>
    </div>
    <div class="returnContent">
        <a class = "return" href = "/maps/favoriteplaceEdit">戻る</a>
    </div>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=initMap" defer></script>
<script>  

    function initMap() {
        // マップを表示
        map = new google.maps.Map(document.getElementById("mapArea"), {
            zoom: 15,
            center: new google.maps.LatLng({{$favoritePlace->latitude}}, {{$favoritePlace->longitude}}),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        
        // 寺院の位置にマーカーを設置
        let marker = new google.maps.Marker({
            map: map,
            position: { lat: {{$favoritePlace->latitude}}, lng: {{$favoritePlace->longitude}} }
        });
        
        //マーカーの吹き出しを追加
        let infoWindow = new google.maps.InfoWindow();
        google.maps.event.addListener(marker, 'click', function() {
              let content = `<strong>{{$favoritePlace->name}} </strong>`
              infoWindow.setContent(content);
              infoWindow.open(map, marker);
    　  });
    　  
    }
    
    function editComment(){
        document.getElementById("comment").innerHTML = `<div class = "comment">
                                                            <form action="/posts/placeComment/{{ $favoritePlace->id }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <input id="editComment" type="hidden" name="comment" value = "{{$favoritePlace->comment}}">
                                                                <trix-editor input="editComment"></trix-editor>
                                                                <input type = "submit" class = "submit" value = "変更">
                                                            </form>
                                                        </div>`;
        //ボタンを非表示にし、フォームを表示
        document.getElementById("show").style.display = 'none';
        document.getElementById("share").style.display = 'none';
        document.getElementById("print").style.display = 'none';
    }
</script>
    
</body>
</html>