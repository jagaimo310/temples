<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>地点詳細表示</title>
    <!--css-->
    <link href="{{ asset('/css/detail.css') }}" rel="stylesheet" />
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
    
    <div class = "placeInfo" >
        <h1><a  class = "searchA" href = "https://www.google.com/maps/search/{{ urlencode(request()->query('name')) }}" target="_blank" rel="noopener noreferrer">{{ request()->query('name') }}</a></h1>
        <!-- お気に入り地点登録用フォーム -->
          <div class = "favoritePlace" >
          @auth
            <form action="/maps" method="POST">
                @csrf
                <input type = "hidden"  name="favoritePlace[name]" value ="{{request()->query('name')}}">
                <input type = "hidden"  name="favoritePlace[place_id]" value ="{{ request()->query('id') }}">
                <input type = "hidden"  name="favoritePlace[latitude]" value ="{{ request()->query('lat') }}">
                <input type = "hidden"  name="favoritePlace[longitude]" value ="{{ request()->query('lng') }}">
                <input type = "hidden" id = "favoritePrefecture" name="favoritePlace[prefecture]">
                <input type = "hidden" id = "favoriteArea" name="favoritePlace[area]">
                <textarea name = "favoritePlace[comment]" placeholder = "メモ（なくても保存可能）" class = "memo"></textarea><br>
                <input type="submit" value="地点登録" class = "point">
            </form>
            @if($errors->any())
              <p class="error">すでに登録済みです</p>
            @endif
          @endauth
  
        </div>
      
        <div id = "website" class = "website"></div>
        <img id = "photo" class = "photo">
        
        
    </div>
    <!-- 検索フォーム -->
    <div class = "form">
        <form class = "travel">
            <select id = "travelMode" class = "travelMode">
              <option value="WALKING">徒歩</option>
              <option value="DRIVING">車</option>
            </select>
            <input type="text" id="startAddress" class = "startAddress" value="現在地">
            <!--お気に入り地点を並べる-->
            @auth
          <div class = "startDropdown" id="startDropdown" >
              @foreach($favoritePlaces as $favoritePlace)
                  <!--data-に値をセットするときはハイフンを入れる様にすること　また、javascriptで呼び出すときはキャメルケースにしなければならない　今回はplaceId-->
                  <div data-start-lat="{{$favoritePlace->latitude}}" data-start-lng="{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div> 
              @endforeach
          </div>
          @endauth
          
            <input type = "hidden" id = "lat">
            <input type = "hidden" id = "lng">
            <input type="button" value="検索" onclick="startPlaces();" class = "submit">
        </form>
    </div>
    <!-- マップ表示 -->
    <div id="mapArea" class = "mapArea"></div>
    <!-- ルート情報 -->
    <div id = "routeInform" class = "routeInform"></div>
    <!--公共交通機関ルートへのURL -->
    <div class = "url">
      <a class = "routeUrl" href = "\maps\navi?id={{ request()->query('id') }}&lat={{ request()->query('lat') }}&lng={{ request()->query('lng') }}&name={{ request()->query('name') }}">公共交通機関でのルート検索はこちら</a>
    </div>
    <div id = "openHours" class = "openingHours"></div>
    <div class="geminiResult">
        <div id = "gemini" class = "gemini">
          <button onclick = "gemini()" class = "geminiButton">GEMINIの解説を見る</button>
        </div>
    </div>
    <!-- レビュー一覧 -->
    
    <div class = "blogResult">
      <h3>アプリレビュー</h3><hr>
      @if(!empty($posts))
        @foreach($posts as $post) 
          <a href="/posts/{{$post->id}}"><h3>{{$post->title}}</h3></a>
          <p>{{$post->temple}}</p>
          <img src="{{$post->image}}" alt="写真">
          <hr>
          <br>
        @endforeach
      @endif
        
      @if(!empty($message))
        <p>{{$message}}</p>
      @endif
      <br>
  </div>
  
    <div class = "mapReview">
      <h3>Google map レビュー</h3>
      <div id="templeReview"></div>
    </div>
      
  </body>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=initMap" defer></script>
<script>
    let map;
    let startMarker;
    let urlParams = new URLSearchParams(window.location.search);
    let templeLat = parseFloat(urlParams.get('lat'));
    let templeLng = parseFloat(urlParams.get('lng'));
    let templeName = urlParams.get('name');
    let templeid = urlParams.get('id');
    
    function initMap() {
        // マップを表示
        map = new google.maps.Map(document.getElementById("mapArea"), {
            zoom: 5,
            center: new google.maps.LatLng(templeLat, templeLng),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        
        // 寺院の位置にマーカーを設置
        let templeMarker = new google.maps.Marker({
            map: map,
            position: { lat: templeLat, lng: templeLng }
        });
        
        //マーカーの吹き出しを追加
        let infoWindow = new google.maps.InfoWindow();
        google.maps.event.addListener(templeMarker, 'click', function() {
              let templeContent = "<strong>" + templeName +"</strong>"
              infoWindow.setContent(templeContent);
              infoWindow.open(map, templeMarker);
    　  });
        
        // ユーザーの現在位置を取得
        navigator.geolocation.getCurrentPosition(function(position) {
          // 緯度・経度を変数に格納
          let currentLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
          // ユーザーの位置にマーカーを表示
          startMarker = new google.maps.Marker({
              map: map,             
              position: currentLatLng 
          });
          //マーカーの吹き出しを追加
          google.maps.event.addListener(startMarker, 'click', function() {
            var markerContent = "<strong>現在位置</strong>"
            infoWindow.setContent(markerContent);
            infoWindow.open(map, startMarker);
          });
          @auth
            //routeSearch()関数に数値を渡す
            let currentLat = parseFloat(position.coords.latitude);
            let currentLng = parseFloat(position.coords.longitude);
            document.getElementById("lat").value = currentLat;
            document.getElementById("lng").value = currentLng;
            routeSearch(currentLat,currentLng);
          @endauth
      },
      // 位置情報の取得に失敗した場合
      function(error) {
          console.error("位置情報の取得に失敗しました: " + error.message);
      });
            
        //レビューと写真の取得
        var service = new google.maps.places.PlacesService(map);

        let request = {
          placeId: templeid, 
          fields:['reviews','photos','address_components','name','opening_hours','url','website','geometry','place_id']
        };
        let reviewHTML = "";
        service.getDetails(request, function(place, status) {
          if (status === google.maps.places.PlacesServiceStatus.OK) {
            @auth
              console.log(place);
              place.address_components.forEach(function(component) {
                  if (component.types.includes("administrative_area_level_1")) {
                      document.getElementById("favoritePrefecture").value = component.long_name; // 都道府県を取得
                  }
                  if (component.types.includes("locality")) {
                      document.getElementById("favoriteArea").value = component.long_name; // 市町村を取得
                  }
              });
            @endauth
              
            
          //写真の表示
            if(place.photos){
              let photo = place.photos;
              const photoUrl = photo[0].getUrl({maxWidth: 750, maxHeight: 600});
              document.getElementById("photo").src = photoUrl;
            }else{
              document.getElementById("photo").src = "";
            }
            //reviewsにある要素をループさせる
            if(place.reviews){
              place.reviews.forEach(function(review) {
                reviewHTML += "<p>評価" + review.rating + "</p>";
                reviewHTML += "<p>" + review.text + "</p>";
                reviewHTML += "<p>" + review.relative_time_description + "</p>";
                reviewHTML += "<hr>";
              });
            }
            document.getElementById("templeReview").innerHTML = reviewHTML;
            
            //公式サイトの取得
            if(place.website){
              document.getElementById("website").innerHTML = `<a href = "${place.website}" target="_blank" rel="noopener noreferrer">公式サイトへ<a>`;
            }else{
              document.getElementById("website").innerHTML = "";
            }
            
            //営業時間の取得
            let hourHTML = "";
            if(place.opening_hours){
              hourHTML += `<h3>営業時間</h3>`;
              place.opening_hours.weekday_text.forEach(function(hour){
                hourHTML += `${hour}<br>`;
              });
            }
            document.getElementById("openHours").innerHTML = `${hourHTML}`;
            
          } else {
            console.error('レビューが取得できませんでした。');
          }
        });
        
            
            
        // autocompleteの記述   
        let startAddress = document.getElementById("startAddress").value;
        // autocomplete機能及び候補選択時の処理 
        const input = document.getElementById("startAddress");
        autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                // 位置が取得できた場合
                var location = place.geometry.location;
                var startAddressLat = parseFloat(location.lat());
                var startAddressLng = parseFloat(location.lng());
                
                // マーカーリセット
                startMarker.setMap(null);
                    
                // 検索地点のマーカー追加
                startMarker = new google.maps.Marker({
                    map: map,
                    position: location
                });
                  
                var autocompletePhoto = place.photos;
                console.log(autocompletePhoto);
                const autocompletePhotoUrl = autocompletePhoto[0].getUrl({maxWidth: 200, maxHeight: 150});  
                console.log(autocompletePhotoUrl);  
                // マーカーの吹き出しを追加
                var infoWindow = new google.maps.InfoWindow();
                google.maps.event.addListener(startMarker, 'click', function() {
                    var markerContent = "<strong>" + place.formatted_address + "</strong><br/>"+
                                        "<img src=" + autocompletePhotoUrl + "/>"
                    infoWindow.setContent(markerContent);
                    infoWindow.open(map, startMarker);
                });
                    
                routeSearch(startAddressLat, startAddressLng);
            } else {
            //こっちになった際にクエリがリセットされて再読み込みされるエラーが発生してる
                alert("場所が見つかりませんでした。");
            }
      });
    }
    
    function startPlaces(){
        let startAddress = document.getElementById("startAddress").value;
        let startLat = document.getElementById("lat").value;
        let startLng = document.getElementById("lng").value;
          if (startAddress == "") {
            return;
          }
          //検索場所の位置情報を取得
          if(startLat && startLng){
            routeSearch(startLat,startLng);
          }else{
            let geocoder = new google.maps.Geocoder();
            geocoder.geocode({
               address: startAddress
               },
              function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {
               
                  //マーカーリセット
                  startMarker.setMap(null);
                  
                  //検索地点のマーカー追加
                  startMarker = new google.maps.Marker({
                      map: map,
                      position: results[0].geometry.location
                  });
                  
                  //マーカーの吹き出しを追加
                  var infoWindow = new google.maps.InfoWindow();
                  google.maps.event.addListener(startMarker, 'click', function() {
                    var markerContent = "<strong>" + startAddress +"</strong>"
                    infoWindow.setContent(markerContent);
                    infoWindow.open(map, startMarker);
                  });
                  
                  var location = results[0].geometry.location;
                  var startAddressLat = parseFloat(location.lat()); // 緯度
                  var startAddressLng = parseFloat(location.lng()); // 経度
                  routeSearch(startAddressLat,startAddressLng);
                  
               }else {
                  alert( startAddress + "：位置情報が取得できませんでした。");
                }
            });
          }
    }
    
    
    
    function routeSearch(positionLat,positionLng){
      var travelMode = document.getElementById("travelMode").value;
      //DirectionsService のオブジェクトを生成
      var directionsService = new google.maps.DirectionsService();
    　//既にルートが表示されている場合そのルートをリセット
      if (window.directionsRenderer) {
        window.directionsRenderer.setMap(null);
    　}
    　//新しくマップ上に引くルートを定義
      window.directionsRenderer = new google.maps.DirectionsRenderer();
    　window.directionsRenderer.setMap(map);
      
      //リクエストの出発点の位置（Empire State Building 出発地点の緯度経度）
      var start = new google.maps.LatLng(positionLat, positionLng);  
      
      //リクエストの終着点の位置（Grand Central Station 到着地点の緯度経度）
      var end = new google.maps.LatLng( templeLat,templeLng);  
      
      // ルートを取得するリクエスト
      var request = {
        origin: start,      // 出発地点の緯度経度
        destination: end,   // 到着地点の緯度経度
        travelMode: travelMode 
      };
      
      //DirectionsService のオブジェクトのメソッドをセットして表示
      directionsService.route(request, function(result, status) {
      
        //ステータスがOKの場合、
        if (status === 'OK') {
          directionsRenderer.setDirections(result); //取得したルート（結果：result）をセット
          //ルート情報を定義し表示
          var route = result.routes[0].legs[0];
          var duration = route.duration.text;
          var distance = route.distance.text;
          document.getElementById("routeInform").innerHTML =
             "<p>所要時間: " + duration + "</p>" +
              "<p>距離: " + distance + "</p>";
        }else{
          alert("ルート情報を取得できませんでした：" );
        }
      });
  
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      @auth
        //type ='hidden'になっているinput要素を制御する
        // input要素を取得
        let hidden = document.getElementById("startAddress");
    
        // inputイベントリスナーを追加
        hidden.addEventListener('input', function() {
            // startのvalueが空かどうかを確認
            if(hidden.value === '') {
                // valueが空の場合srartLatLngも空にする
                document.getElementById('lat').value = "";
                document.getElementById('lng').value = "";
            }
        });
        
        //ドロップダウン用
        let start = document.getElementById('startAddress');
        let startDropdown = document.getElementById('startDropdown');

        // ドロップダウンを表示
        start.addEventListener('focus', function() {
            startDropdown.style.display = 'block';
        });

        // ドロップダウンのアイテムがクリックされた時の処理
        startDropdown.addEventListener('click', function(event) {
            if (event.target && event.target.matches('div')) {
                //eventはクリック、targetはそれが実行された位置
                start.value = event.target.textContent;
                startDropdown.style.display = 'none';
                //htmlのdata-はjavacriptではdataset.〜で取得する。またハイフンはキャメルケースで書き直すこと
                document.getElementById('lat').value = event.target.dataset.startLat;
                document.getElementById('lng').value = event.target.dataset.startLng;
            }
        });
        
        // ドロップダウン以外をクリックするとドロップダウンを非表示にする
        document.addEventListener('click', function(event) {
            if (!start.contains(event.target) && !startDropdown.contains(event.target)) {
                startDropdown.style.display = 'none';
            }
        });
    @endauth
    });
  
  //gemini解説処理  
  function gemini(){
    document.getElementById("gemini").innerHTML = "<p>生成中…</p>";
    const GEMINI_API_KEY = `{{ config("services.gemini.apikey") }}`;
    let url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${GEMINI_API_KEY}`;
    //形式指定がある　公式ドキュメントをチェック
    let request = {
      contents: [{
          parts: [{ text: `${templeName}について500字以内で教えてください。` }]
        }]
    };
    
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(request)
    })
      .then(response => response.json()) 
      .then(data => {
        // 結果を出力
        document.getElementById("gemini").innerHTML = `<h3>Gemini解説</h3>
                                                        <hr>
                                                        ${data.candidates[0].content.parts[0].text}`;
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById("gemini").innerHTML = `<h3>Gemini解説</h3>
                                                        <hr>
                                                        <p>生成に失敗しました。</p>
                                                        <button onclick = "gemini()" class = "geminiButton">再生成</button>`;
      });
  }
</script>