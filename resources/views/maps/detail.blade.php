<body>
    <div>
        <h1>{{ $templeName = request()->query('name') }}</h1>
    </div>
    <div>
        <form>
            <select id = "travelMode">
              <option value="DRIVING">ドライブ</option>
              <option value="WALKING">徒歩</option>
            </select>
            <input type="text" id="startAddress" value="東京" style="width: 200px">
            <input type="button" value="検索" onclick="startPlaces();">
        </form>
    </div>
    <div id="mapArea" style="width:700px; height:400px;"></div>
    <div id = "routeInform"></div>
    <h3>Google map レビュー</h3>
    <div id="templeReview"></div>
  </body>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBnJDoMPl2eOu210vjG49G-7MUHW2l8do&libraries=places&callback=initMap" async defer></script>
<script type="text/javascript">
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
        var templeMarker = new google.maps.Marker({
            map: map,
            position: { lat: templeLat, lng: templeLng }
        });
        
        //マーカーの吹き出しを追加
        var infoWindow = new google.maps.InfoWindow();
        google.maps.event.addListener(templeMarker, 'click', function() {
              var templeContent = "<strong>" + templeName +"</strong>"
              infoWindow.setContent(templeContent);
              infoWindow.open(map, templeMarker);
    　  });
        
        // ユーザーの現在位置を取得
        navigator.geolocation.getCurrentPosition(
            // 位置情報の取得に成功した場合
            function(position) {
                // 緯度・経度を変数に格納
                var currentLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
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
                
                //route()関数に数値を渡す
                let currentLat = parseFloat(position.coords.latitude);
                let currentLng = parseFloat(position.coords.longitude);
                
                routeSearch(currentLat,currentLng);
            },
            // 位置情報の取得に失敗した場合
            function(error) {
                console.error("位置情報の取得に失敗しました: " + error.message);
            });
            
        //レビューの取得
        var service = new google.maps.places.PlacesService(map);

        var request = {
          placeId: templeid, 
          fields:['reviews']
        };
        var reviewHTML = "";
        service.getDetails(request, function(place, status) {
          if (status === google.maps.places.PlacesServiceStatus.OK) {
          var reviews= place.reviews;
          console.log(reviews);
          //reviewsにある要素をループさせる
            place.reviews.forEach(function(review) {
              reviewHTML += "<p>評価" + review.rating + "</p>";
              reviewHTML += "<p>" + review.text + "</p>";
              reviewHTML += "<p>" + review.relative_time_description + "</p>";
              reviewHTML += "<hr>";
            });
          document.getElementById("templeReview").innerHTML = reviewHTML;    
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
                    
                // マーカーの吹き出しを追加
                var infoWindow = new google.maps.InfoWindow();
                google.maps.event.addListener(startMarker, 'click', function() {
                    var markerContent = "<strong>" + place.formatted_address + "</strong>"
                    infoWindow.setContent(markerContent);
                    infoWindow.open(map, startMarker);
                });
                    
                routeSearch(startAddressLat, startAddressLng);
            } else {
                alert("場所が見つかりませんでした。");
            }
      });
    }
    
    function startPlaces(){
        var startAddress = document.getElementById("startAddress").value;
          if (startAddress == "") {
            return;
          }
          //検索場所の位置情報を取得
          var geocoder = new google.maps.Geocoder();
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
        travelMode: travelMode //トラベルモード
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

</script>