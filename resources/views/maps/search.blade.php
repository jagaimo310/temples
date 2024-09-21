<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ピンポイント検索</title>
</head>
<body>
    <!--ヘッダー-->
    <div class=header>
        <a href="/">トップ</a>
        <a href="/register">新規登録</a>
        <a href = "/posts/mypage">ログイン・マイページ</a>
        <a href="/posts/create">投稿</a>
        <a href="/maps/place">地点検索</a>
        <a href="/maps/search">ピンポイント検索</a>
        <a href="/maps/severalRoute">複数地点検索</a>
        <a href="/maps/navi">公共交通機関</a>
    </div>
    
    <div name = "title">
        <h1>ピンポイント検索</h1>
    </div>
    
    <form action = "/maps/search" method = "GET" id = "form">
        <input type = "text" id= "place" name = "blogSearch" >
        <input type = "button" value = "検索" onclick  = "getPlace();">
    </form>
    
    <div id= "mapArea" style="width:700px; height:400px;"></div>
    
    <div id = "placeName"></div>
    <!-- お気に入り地点登録用フォーム -->
  <div name = "favoritePlace">
  @auth
      <form action="/maps" method="POST" >
          @csrf
          <input type = "hidden"  name="favoritePlace[name]" id = 'name' >
          <input type = "hidden"  name="favoritePlace[place_id]" id = 'place_id' >
          <input type = "hidden"  name="favoritePlace[latitude]" id = 'latitude' >
          <input type = "hidden"  name="favoritePlace[longitude]" id = 'longitude' >
          <input type = "hidden" id = "favoritePrefecture" name="favoritePlace[prefecture]">
          <input type = "hidden" id = "favoriteArea" name="favoritePlace[area]">
          <div id = "submit"></div>
      </form>
  @endauth
</div>
    <img id= "photo" >
    <div id= "review" ></div>
    
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=initMap" defer></script>
    <script type="text/javascript">
        
        let map;
        let marker;
        
       function initMap(){
            //最初のマップ設定
            map = new google.maps.Map(document.getElementById("mapArea"), {
                zoom: 5,
                center: new google.maps.LatLng(36,138),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            
            
            //オートコンプリート機能　オートコンプリート実行時はここでマーカーを打つ
            let place = document.getElementById("place");
            Autocomplete = new google.maps.places.Autocomplete(place);
            Autocomplete.addListener('place_changed', function() {
              const placeInfo = Autocomplete.getPlace();
              if (placeInfo.geometry) {
              
                  //マーカーリセット
                  if(marker){
                    marker.setMap(null);  
                  }
                  
                  
                  //地図情報の変更
                  let place_id = placeInfo.place_id;
                  let location = placeInfo.geometry.location;
                  map.setCenter(location);
                  map.setZoom(13);
                  
                  //マーカーの表示
                  marker = new google.maps.Marker({
                      map: map,
                      position: location,
                  });
                  
                  //関数に繋げる
                 detailPlace(place_id); 
                  
                } else {
                   alert("場所が見つかりませんでした。");
                }
                
          });
            
        }
        
        function getPlace(){
          let placeName = document.getElementById('place').value;

          //placeIdがある場合はそのままdetailPlaceに繋げる
          if(placeName){
            
            //placeNameのみの場合はジオコーディング
            let geocoder = new google.maps.Geocoder();
            geocoder.geocode({
               address: placeName
             },
            function(results, status) {
             if (status == google.maps.GeocoderStatus.OK) {
                //マーカーリセット
                  if(marker){
                    marker.setMap(null);  
                  }
                
                //地図情報の変更
                map.setCenter(results[0].geometry.location);
                map.setZoom(13);
                
                //検索地点のマーカー追加
                startMarker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location
                });
                //place_idを持ってきて渡す
                let place_id = results[0].place_id;
                detailPlace(place_id);
                
             }else {
                alert( startAddress + "：位置情報が取得できませんでした。");
              }
            });
            
          }
        }
        
        
        function detailPlace(placeId){
            
            var service = new google.maps.places.PlacesService(map);

            let request = {
              placeId: placeId
            };
            let reviewHTML = "";
            service.getDetails(request, function(place, status) {
              if (status === google.maps.places.PlacesServiceStatus.OK) {
              let reviews = place.reviews;
              console.log(place);
              //情報があれば写真とレビューを取得して並べる
              if(place.reviews){
                let reviews = place.reviews;
                //reviewsにある要素をループさせる
                  place.reviews.forEach(function(review) {
                    reviewHTML += "<p>評価" + review.rating + "</p>";
                    reviewHTML += "<p>" + review.text + "</p>";
                    reviewHTML += "<p>" + review.relative_time_description + "</p>";
                    reviewHTML += "<hr>";
                  });
                document.getElementById("review").innerHTML = reviewHTML;  
              } 
              
              if(place.photos){
                //写真の表示
                let photo = place.photos;
                const photoUrl = photo[0].getUrl({maxWidth: 750, maxHeight: 600});
                document.getElementById("photo").src = photoUrl;
              }
              //お気に入り地点保存用の値をセット
              document.getElementById('placeName').innerHTML = `<h1>${place.name}</h1>`;
              document.getElementById('name').value = place.name;
              document.getElementById('place_id').value = place.place_id;
              document.getElementById('latitude').value = parseFloat(place.geometry.location.lat());
              document.getElementById('longitude').value = parseFloat(place.geometry.location.lng());
              document.getElementById('submit').innerHTML = '<input type="submit" value="地点登録">';
              
              //県と市をセット
              place.address_components.forEach(function(component) {
                  if (component.types.includes("administrative_area_level_1")) {
                      document.getElementById("favoritePrefecture").value = component.long_name; 
                  }
                  if (component.types.includes("locality")) {
                      document.getElementById("favoriteArea").value = component.long_name; 
                  }
              });
            } else {
              console.error('レビューが取得できませんでした。');
            }
          });
        }
      
      
      
       
    </script>
</body>
</html>