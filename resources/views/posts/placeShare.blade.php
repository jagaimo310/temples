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
    <!--trixのファイル添付ボタンを非表示にする -->
    <style>
    trix-toolbar [data-trix-button-group="file-tools"] {
        display: none; 
    }
    </style>
</head>

<body>
    <a class = "title" href = "/maps/{{$favoritePlace->name}}?lat={{$favoritePlace->latitude}}&lng={{$favoritePlace->longitude}}&id={{$favoritePlace->place_id}}&name={{$favoritePlace->name}}" class = "favolite">{{$favoritePlace->name}}</a><br>
    <p class = "prefecture">{{$favoritePlace->prefecture}} {{$favoritePlace->area}}</p>
    <div id = "mapArea" class = "mapArea"></div><br>
    <div id = "show" class = "memoForm">
        <p>{!!$favoritePlace->comment!!}</p>
    </div>
    <div id = "share"  class = "share">
        <button onclick = "window.print()" class = "print">印刷</button>
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
</script>
    
</body>
</html>