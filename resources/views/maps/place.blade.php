<!DOCTYPE html>
<html lang="ja">
 
 <head>
    <meta charset="utf-8">
   <title>地点検索</title>
    <!--css-->
    <link href="{{ asset('/css/place.css') }}" rel="stylesheet" />
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

let map;
let placesList = [];
let markers = [];
let googlemap_apiKey = @json(config('services.google-map.apikey'));



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
      nearbySearch(currentLat,currentLng,currentRadius,currentZoom);
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
          nearbySearch(addressLat,addressLng,radius,zoom);
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
        nearbySearch(autoCompleteLat,autoCompleteLng,distance,zoom);
    }else{
        geocoder.geocode({
          　address: place
        },
        function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            let location = results[0].geometry.location;
            let geoLat = location.lat();
            let geoLng = location.lng();
            nearbySearch(geoLat,geoLng,distance,zoom);
          }else {
            alert("位置情報が取得できませんでした。");
          }
        });
    }
  }
}



async function nearbySearch(lat,lng,radius,zoom) {
  //@ts-ignore
  const { Place, SearchNearbyRankPreference } = await google.maps.importLibrary(
    "places",
  );
  document.getElementById("results").innerHTML = "<h3 style='text-align: center; display: block;'>Now Loading...</h3>";
   let category = document.getElementById("category").value;
   let keyword = document.getElementById("keyword").value;
  //地図情報の変更
   let latLng = new google.maps.LatLng(lat, lng);
   map.setCenter(latLng);
   map.setZoom(zoom);

  const request = {
    // required parameters
    fields: ["displayName","location","id","rating","userRatingCount","photos"],
    locationRestriction: {
      center: latLng,
      keyword:keyword,
      radius: Number(radius),
    },
    // optional parameters
    includedPrimaryTypes: [category],
    maxResultCount: 20,
    rankPreference: SearchNearbyRankPreference.POPULARITY,
    language: "ja",
  };
  const { places } = await Place.searchNearby(request);

  if (places.length) {
    displayResults(places);
  } else {
    console.log("No results");
    //結果表示
    document.getElementById("results").innerHTML = "結果なし";
  }
}


//結果処理
function displayResults(placesList) {
  //結果表示のHTMLタグを組み立てる
  let resultHTML = "<ol>";
  let marker = [];
  
  //マーカーのリセット
  clearMarkers();
  
  //並び変え
  placesList.sort(function(a, b) {
        if (a.userRatingCount > b.userRatingCount) return -1;
        if (a.userRatingCount < b.userRatingCount) return 1;
        return 0;
      });
  
  console.log(placesList);
  
  for (var i = 0; i < placesList.length; i++) {
    place = placesList[i];
    //ここで各place事にマーカーの処理をする
    let infoWindow = new google.maps.InfoWindow();
     marker = new google.maps.Marker({
        map: map,
        position: place.location
    });
    
     // マーカーをmarkers配列に追加
    markers.push(marker);
    
    (function(marker, place) {
    google.maps.event.addListener(marker, 'click', function() {
       
        if(place.photos.length === 0){
          const photoUrl = null;
          console.log("if");
          
           //表示内容
          let markerContent = "<strong>" + place.displayName + "</strong><br>" +
                        "評価: " + place.rating + "<br>" +
                        "レビュー数: " + place.userRatingCount
                        

          infoWindow.setContent(markerContent);
          infoWindow.open(map, marker);
          
        }else{
          const photos = place.photos;
      
          // 画像URLを構築
          let photoUrl = "https://places.googleapis.com/v1/"+photos[0].name+"/media?key="+googlemap_apiKey+"&maxHeightPx=200&maxWidthPx=150";

          //表示内容
          let markerContent = "<strong>" + place.displayName + "</strong><br>" +
                        "評価: " + place.rating + "<br>" +
                        "レビュー数: " + place.userRatingCount + "<br>" +
                        "<img alt = 写真がありません src="+photoUrl+">"


          infoWindow.setContent(markerContent);
          infoWindow.open(map, marker);
        }
        
        

      
    });
  })(marker, place);
   
   
   let user_ratings = place.userRatingCount;
   let rating = place.rating;
   let name = place.displayName;
    //ratingがないのものは「---」に表示変更
    if(user_ratings === null){
      console.log("if");
      rating = "---";
      user_ratings = "---";
    } 
    
    //表示内容（評価＋名称）
    let content = "【" + rating + "】 " + name + "【" + user_ratings + "】 " ;
    
    resultHTML += "<li>";
    resultHTML += "<a class = 'url' href=/maps/"+ encodeURIComponent(name) +"?lat="+ place.location.lat() +"&lng="+ place.location.lng() + "&id="+ place.id + "&name=" + encodeURIComponent(name) +">";
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