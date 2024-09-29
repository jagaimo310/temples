<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 
 <head>
    <meta charset="utf-8">
   <title>地点検索</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!--css-->
    <link href="{{ asset('/css/place.css') }}" rel="stylesheet" />
</head>  


<body>
  <!--ヘッダー-->
  <div class = "header">
      <a href="/">トップ</a>
      <a href="/register">新規登録</a>
      <a href = "/posts/mypage">ログイン・マイページ</a>
      <a href = "/posts/postsAll">投稿表示</a>
      <a href="/posts/create">投稿</a>
      <a href="/maps/place">地点検索</a>
      <a href="/maps/search">ピンポイント検索</a>
      <a href="/maps/severalRoute">複数地点検索</a>
      <a href="/maps/navi">公共交通機関</a>
  </div>
  
<!--場所検索フォーム-->
<div class = "serchForm">
  <form>
      <select id = "distance">
          <option value = "14">2000</option>
          <option value = "16">500</option>
          <option value = "15">1000</option>
          <option value = "13">3000</option>
          <option value = "12">5000</option>  
      </select>
      <lavel for = "distance">m</lavel>
      <select id = "category">
          <option value = "tourist_attraction">観光</option>
          <option value = "restaurant">食事</option>
      </select>
      
      <input type = "text" id = "keyword" placeholder = "Keyword">
      <input type="text" id="place" class="place">

      <!--ログイン時にお気に入り地点を表示する-->
      @auth
      <div id="placeDropdown" class="placeDropdown">
          @foreach($favoritePlaces as $favoritePlace)
              <div data-place-lat="{{$favoritePlace->latitude}}" data-place-lng="{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div>
          @endforeach
      </div>
      @endauth
    
      <input type = "hidden" id = "lat">
      <input type = "hidden" id = "lng">
      <input type="button" value="検索" onclick="getPlaces();">
  </form>
</div>


 <div id="mapArea" class = "mapArea"></div> 
 


<div id="results" class = "results"></div>
</body>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=initMap" defer></script>
<script type="text/javascript">

var map;
var placesList = [];
var markers = [];


//図の初期表示
function initMap() {
  map = new google.maps.Map(document.getElementById("mapArea"), {
    zoom: 5,
    center: new google.maps.LatLng(36,138),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });
  
  //現在地を検索
  navigator.geolocation.getCurrentPosition(function(position) {
      // 緯度・経度を変数に格納
      let currentLat = position.coords.latitude; 
      let currentLng = position.coords.longitude;
      let currentRadius = 2000;
      let currentZoom = 14;
      document.getElementById("lat").value = currentLat;
      document.getElementById("lng").value = currentLng;
      document.getElementById("place").value = "現在地";
      startNearbySearch(currentLat,currentLng,currentRadius,currentZoom);
  },
  // 位置情報の取得に失敗した場合
  function(error) {
      console.error("位置情報の取得に失敗しました: " + error.message);
  });
  
  // autocompleteの記述 
  let place = document.getElementById("place");
  Autocomplete = new google.maps.places.Autocomplete(place);
  Autocomplete.addListener('place_changed', function() {
      const place = Autocomplete.getPlace();
      if (place.geometry) {
          // 位置が取得できた場合
          let location = place.geometry.location;
          let addressLat = parseFloat(location.lat());
          let addressLng = parseFloat(location.lng());
          document.getElementById("lat").value = addressLat;
          document.getElementById("lng").value = addressLng;
          let radius = document.getElementById("distance").options[document.getElementById("distance").selectedIndex].text;
          let zoom = parseFloat(document.getElementById("distance").value);
          //placesList配列を初期化
          placesList = new Array();
          startNearbySearch(addressLat,addressLng,radius,zoom);
      } else {
          alert("場所が見つかりませんでした。");
      }
  });
}

// マーカーを削除用関数
function clearMarkers() {
    for (let i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];
}

 //検索場所を調べる
function getPlaces(){
  //結果表示クリア
  document.getElementById("results").innerHTML = "";
  //placesList配列を初期化
  placesList = new Array();
  
  //入力した値を取得
  let place = document.getElementById("place").value;
  let autoCompleteLat = document.getElementById("lat").value;
  let autoCompleteLng = document.getElementById("lng").value;
  let selectElement = document.getElementById("distance");
  let zoom = parseFloat(selectElement.value);
  let distance = selectElement.options[selectElement.selectedIndex].text;
  
  //入力を確認
  if(place && zoom){
    let geocoder = new google.maps.Geocoder();
    //座標がわかっている場合（hiddenに値が入っている場合）
    if(autoCompleteLat&&autoCompleteLng){
        startNearbySearch(autoCompleteLat,autoCompleteLng,distance,zoom);
    }else{
        geocoder.geocode({
          　address: place
        },
        function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            let location = results[0].geometry.location;
            let geoLat = location.lat();
            let geoLng = location.lng();
            startNearbySearch(geoLat,geoLng,distance,zoom);    
          }else {
            alert("位置情報が取得できませんでした。");
          }
        });
    }
  }
}

//地図情報の変更及び検索情報から周囲の寺院の情報を検索
function startNearbySearch(lat,lng,radius,zoom){
  //読み込み中表示
  document.getElementById("results").innerHTML = "Now Loading...";
  let category = document.getElementById("category").value;
  let keyword = document.getElementById("keyword").value;
  //地図情報の変更
  let latLng = new google.maps.LatLng(lat, lng);
  map.setCenter(latLng);
  map.setZoom(zoom);

  
  //PlacesServiceインスタンス生成
  var service = new google.maps.places.PlacesService(map);
 
  //周辺検索
  service.nearbySearch(
    {
      location: latLng,
      radius: radius,
      type: [category],
      keyword:keyword,
      language: 'ja'
    },
    paginate
  );
}

function paginate(results, status, pagination) {
  if (status == google.maps.places.PlacesServiceStatus.OK) {
    
    // 検索結果をplacesList配列に連結
    placesList = placesList.concat(results);

    if (pagination.hasNextPage) {
      // 1秒待ってから次の検索結果を取得 setTimeoutがないとelse後の処理がページごとに実行されるので絶対に必要
      setTimeout(function() {
        pagination.nextPage();
      }, 1000);
    } else {
      // 最後のページに到達したら、placesListをソート
      placesList.sort(function(a, b) {
        if (a.user_ratings_total > b.user_ratings_total) return -1;
        if (a.user_ratings_total < b.user_ratings_total) return 1;
        return 0;
      });
      console.log(placesList);
      // placesListの上位20件のみを保持
      placesList = placesList.slice(0, 20);
      // 全てのデータがplacesListに追加された後にdisplayResultsを呼び出す
      displayResults(placesList);
    }
  } else {
    // 検索失敗時
    document.getElementById("results").innerHTML = "結果が見つかりませんでした。";
  }
}




//周辺情報表示及びマーカーのセット
//results : 周辺情報検索結果
//status ： 実行結果ステータス
function displayResults(placesList) {
  //結果表示のHTMLタグを組み立てる
  var resultHTML = "<ol>";
  var marker = [];
  
  //マーカーのリセット
  clearMarkers();
  
  for (var i = 0; i < placesList.length; i++) {
    place = placesList[i];
    
    //ここで各place事にマーカーの処理をする
    let infoWindow = new google.maps.InfoWindow();
     marker = new google.maps.Marker({
        map: map,
        position: place.geometry.location
    });
    
     // マーカーをmarkers配列に追加
    markers.push(marker);
    
    (function(marker, place) {
    google.maps.event.addListener(marker, 'click', function() {
       
        if(place.photos === void 0){
          const photoUrl = null;
          console.log("if");
          
           //表示内容
          var markerContent = "<strong>" + place.name + "</strong><br>" +
                        "評価: " + place.rating + "<br>" +
                        "レビュー数: " + place.user_ratings_total
                        

          infoWindow.setContent(markerContent);
          infoWindow.open(map, marker);
          
        }else{
          const photos = place.photos;
          let photoUrl = photos[0].getUrl({maxWidth: 200, maxHeight: 150});
          //表示内容
          var markerContent = "<strong>" + place.name + "</strong><br>" +
                        "評価: " + place.rating + "<br>" +
                        "レビュー数: " + place.user_ratings_total + "<br>" +
                        "<img alt = 写真がありません src=" + photoUrl + "/>"

          infoWindow.setContent(markerContent);
          infoWindow.open(map, marker);
        }
        
        

      
    });
  })(marker, place);
    
    
    
    //評価を投稿したユーザー数を表示
    var user_ratings = place.user_ratings_total;
    
    //ratingがないのものは「---」に表示変更
    var rating = place.rating;
    if(rating == -1) rating = "---";
    
    //表示内容（評価＋名称）
    var content = "【" + rating + "】 " + place.name + "【" + user_ratings + "】 " ;
    var name = place.name;
    
    resultHTML += "<li>";
    resultHTML += "<a class = 'url' href=/maps/"+ encodeURIComponent(name) +"?lat="+ place.geometry.location.lat() +"&lng="+ place.geometry.location.lng() + "&id="+ place.place_id + "&name=" + encodeURIComponent(name) +">";
    resultHTML += content;
    resultHTML += "</a>";
    resultHTML += "</li>";
    
    

  }
  
  resultHTML += "</ol>";
  
  //結果表示
  document.getElementById("results").innerHTML = resultHTML;
}

//type ='hidden'になっているinput要素を制御するための関数
  document.addEventListener('DOMContentLoaded', function() {
      // input要素を取得
      let hidden = document.getElementById("place");
  
      // inputイベントリスナーを追加
      hidden.addEventListener('input', function() {
          // startのvalueが空かどうかを確認
          if(hidden.value === '') {
              // valueが空の場合srartLatLngも空にする
              document.getElementById('lat').value = "";
              document.getElementById('lng').value = "";
          }
      });
      
      //ドロップダウン制御用
      @auth
        let place = document.getElementById('place');
        let placeDropdown = document.getElementById('placeDropdown');
        
        // ドロップダウンを表示
        place.addEventListener('focus', function() {
            placeDropdown.style.display = 'block';
        });
        
        // ドロップダウンのアイテムがクリックされた時の処理
        placeDropdown.addEventListener('click', function(event) {
            if (event.target && event.target.matches('div')) {
                //eventはクリック、targetはそれが実行された位置
                place.value = event.target.textContent;
                placeDropdown.style.display = 'none';
                //htmlのdata-はjavacriptではdataset.〜で取得する。またハイフンはキャメルケースで書き直すこと
                document.getElementById('lat').value = event.target.dataset.placeLat;
                document.getElementById('lng').value = event.target.dataset.placeLng;
            }
        });
        
        // ドロップダウン以外をクリックするとドロップダウンを非表示にする
        document.addEventListener('click', function(event) {
            if (!place.contains(event.target) && !placeDropdown.contains(event.target)) {
                placeDropdown.style.display = 'none';
            }
        });
        
      @endauth
      
  });

</script>