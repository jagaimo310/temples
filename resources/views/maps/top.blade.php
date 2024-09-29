<!DOCTYPE html>
<html lang="ja">
 
 <head>
    <meta charset="utf-8">
   <title>routecraft</title>
    
    <!--css-->
    <link href="{{ asset('/css/top.css') }}" rel="stylesheet" />
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

<div class = "serchForm">
  <!--場所検索フォーム-->
  <form>
      <input id = "keyword" placeholder = "Keyword">
      </select>
    <label for="prefecture">都道府県:</label>
      <select id="prefecture">
          <option value="">選択してください</option>
      </select>
      
    <label for="city">市区町村:</label>
      <select id="city">
          <option value="">選択しない</option>
      </select>
      
      <input type="button" value="検索" onclick="getPlaces();">
  </form>
</div>

 <div id = 'resultName' class = "resultName"></div>
 <div id="mapArea" class = "mapArea"></div> 
 
<div id="results" class = "results"></div>



</body>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=initMap" defer></script>
<script type="text/javascript">

let map;
let placesList = [];
let markers = [];
let clickPlace;

//図の初期表示
function initMap() {
  map = new google.maps.Map(document.getElementById("mapArea"), {
    zoom: 5,
    center: new google.maps.LatLng(36,138),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });
  
  //クリック時の処理
  map.addListener('click', function(event) {
    //マーカーリセット
    clearMarkers();
    let clickLatlng = event.latLng;
    //クリックした位置の緯度経度を取得したのち、逆ジオコーディングで都道府県と市区町村を出してそれをジオコーディングして緯度経度（まとめた形で）をstartNearbySearchに送信する
    let geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      location: clickLatlng
    },
    function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        let prefecture;  
        let city;        
        
        results[0].address_components.forEach(function(component) {
            if (component.types.includes("administrative_area_level_1")) {
                prefecture = component.long_name; // 都道府県を取得
            }
            if (component.types.includes("locality")) {
                city = component.long_name; // 市町村を取得
            }
        });
        
        
        if(typeof prefecture === "undefined" && typeof city === "undefined"){
          alert("都道府県の情報が取得できませんでした。");
        }else{
          //検索用のaddressInput,addressRadius,addressZoomを定義
          let addressInput;
          let addressZoom;
          let addressRadius
          //clickPlaceの値で処理を変更　undifined、もしくは違う県の値が入っていれば県のみで検索、同じ県が入っていれば市を入れて検索
          if(typeof clickPlace === "undefined"||clickPlace != prefecture){
            clickPlace = prefecture;
            addressInput = prefecture;
            //検索範囲、ズームを変数にして送信
            addressRadius = 50000;
            addressZoom = 8;
          }else if(clickPlace == prefecture){
            clickPlace = prefecture;
            //県と市の名前を合わせる
            addressInput = prefecture + city;
            //検索範囲、ズームを変数にして送信
            addressRadius = 8000;
            addressZoom = 12;
          }
      
          //確認用
          document.getElementById('resultName').innerHTML = `<h4>${addressInput}</h4>`;
          
          geocoder.geocode({
            address: addressInput
          },
          function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              //取得した緯度・経度を使って周辺検索
              startNearbySearch(results[0].geometry.location,addressRadius,addressZoom);
            }
            else {
              alert(addressInput + "：位置情報が取得できませんでした。");
            }
          });
        }
      }
      else {
        alert(addressInput + "：位置情報が取得できませんでした。");
      }
    });
  });

}

let prefectureSelect = document.getElementById('prefecture');
// 都道府県及び市区町村のデータを使用する

// APIキーを設定
const API_KEY = '{{ config("services.resas.apikey") }}';

// 都道府県データを取得
fetch('https://opendata.resas-portal.go.jp/api/v1/prefectures', {
    method: 'GET',
    headers: {
        'X-API-KEY': API_KEY
    }
})
.then(response => response.json())
.then(data => {
    const prefectures = data.result;
    let prefectureSelect = document.getElementById('prefecture');

    // 都道府県のオプションを追加
    prefectures.forEach(function(prefecture){
        const option = document.createElement('option');
        option.value = prefecture.prefCode;
        option.textContent = prefecture.prefName;
        prefectureSelect.appendChild(option);
    });

    // 都道府県が選択されたときに市区町村を取得するイベントリスナーを追加
    prefectureSelect.addEventListener('change', function() {
        const prefCode = this.value;
        if (prefCode) {
            fetchCities(prefCode);
        } else {
            clearCities();
        }
    });
})
.catch(error => console.error('Error:', error));

// 市区町村データを取得
function fetchCities(prefCode) {
    fetch(`https://opendata.resas-portal.go.jp/api/v1/cities?prefCode=${prefCode}`, {
        method: 'GET',
        headers: {
            'X-API-KEY': API_KEY
        }
    })
    .then(response => response.json())
    .then(data => {
        const cities = data.result;
        const citySelect = document.getElementById('city');
        clearCities();

        // 市区町村のオプションを追加
        cities.forEach(function(city) {
            const option = document.createElement('option');
            option.value = city.cityCode;
            option.textContent = city.cityName;
            citySelect.appendChild(option);
        });
    })
    .catch(error => console.error('Error:', error));
}

// 市区町村リストをクリアする関数
function clearCities() {
    let citySelect = document.getElementById('city');
    citySelect.innerHTML = '<option value="">選択しない</option>';
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
   // マーカーを削除
  clearMarkers();
  //結果表示クリア
  document.getElementById("results").innerHTML = "";
  //placesList配列を初期化
  placesList = new Array();
  
  //入力した検索場所を取得
  var prefectureSelect = document.getElementById("prefecture");
  var citySelect = document.getElementById("city");
  var cityValue = document.getElementById("city").value;
  var prefecture = prefectureSelect.options[prefectureSelect.selectedIndex].text;
  var city = citySelect.options[citySelect.selectedIndex].text;
  var addressInput = prefecture + city;
  //都道府県が指定されていなかった場合
  if (prefecture === "選択してください") {
    return; 
  }
  //市区町村が選択されなかった場合
  var geocoder = new google.maps.Geocoder();
  if (cityValue === "") {
    geocoder.geocode({
      address: prefecture
    },
    function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        //検索範囲指定 radiusの最大値である５０ｋｍに設定
        var radius = 50000;
        //マップ範囲指定
        var zoom = 8;
        //結果確認用
        document.getElementById('resultName').innerHTML = `<h4>${prefecture}</h4>`;
        //取得した緯度・経度を使って周辺検索
        startNearbySearch(results[0].geometry.location,radius,zoom);
      }
      else {
        alert(addressInput + "：位置情報が取得できませんでした。");
      }
    });
  }
  else{
  //市区町村が選択された場合
  //検索場所の位置情報を取得
    geocoder.geocode({
        address: addressInput
      },
      function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          //検索範囲指定
          var radius = 8000;
          //マップ範囲指定
          var zoom = 12;
          //結果確認用
          document.getElementById('resultName').innerHTML = `<h4>${addressInput}</h4>`;
          //取得した緯度・経度を使って周辺検索
          startNearbySearch(results[0].geometry.location,radius,zoom);
        }
        else {
          alert(addressInput + "：位置情報が取得できませんでした。");
        }
      });
  }


}

//地図情報の変更及び検索情報から周囲の寺院の情報を検索
function startNearbySearch(latLng,radius,zoom){
  placesList = [];
  //読み込み中表示
  document.getElementById("results").innerHTML = "Now Loading...";
  
  //地図情報の変更
  map.setCenter(latLng);
  map.setZoom(zoom);

  
  //PlacesServiceインスタンス生成
  var service = new google.maps.places.PlacesService(map);
 
  let keyword = document.getElementById("keyword").value;

  //周辺検索
  service.nearbySearch(
    {
      location: latLng,
      radius: radius,
      keyword: keyword,
      type: ['tourist_attraction'],
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


</script>
