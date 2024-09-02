<body>

<form>
  <label for="category">寺社仏閣:</label>
    <select id = "category">
      <option value="神社">神社</option>
      <option value="寺院">寺院</option>
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


 <div id="mapArea" style="width:700px; height:400px;"></div> 
 


結果<br />
<div id="results" style="width: 700px; height: 200px; border: 1px dotted; padding: 10px; overflow-y: scroll; background: white;"></div>
</body>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBnJDoMPl2eOu210vjG49G-7MUHW2l8do&libraries=places&callback=initMap" async defer></script>
<script type="text/javascript">

var map;
var placesList;
var markers = [];

let prefectureSelect = document.getElementById('prefecture');
// 都道府県及び市区町村のデータを使用する

// APIキーを設定
const API_KEY = 'wye7bMQ7Z481UB8C3PkQftnhrOmy99Ud1zcUMUvS';

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
    prefectures.forEach(prefecture => {
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


//図の初期表示
function initMap() {
  map = new google.maps.Map(document.getElementById("mapArea"), {
    zoom: 5,
    center: new google.maps.LatLng(36,138),
    mapTypeId: google.maps.MapTypeId.ROADMAP
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
        //検索範囲指定
        var radius = 60000;
        //マップ範囲指定
        var zoom = 8;
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
  //読み込み中表示
  document.getElementById("results").innerHTML = "Now Loading...";
  
  //地図情報の変更
  map.setCenter(latLng);
  map.setZoom(zoom);

  
  //PlacesServiceインスタンス生成
  var service = new google.maps.places.PlacesService(map);
 
  let category = document.getElementById("category").value;

  //周辺検索
  service.nearbySearch(
    {
      location: latLng,
      radius: radius,
      keyword: category,
      language: 'ja'
    },
    displayResults
  );

  
  
}

//周辺情報表示及びマーカーのセット
//results : 周辺情報検索結果
//status ： 実行結果ステータス
function displayResults(results, status) {
    
    
  if(status == google.maps.places.PlacesServiceStatus.OK) {
  
    //検索結果をplacesList配列に連結

    placesList = results;
    
      //ratingの降順でソート（連想配列ソート）
      placesList.sort(function(a,b){
        if(a.user_ratings_total > b.user_ratings_total) return -1;
        if(a.user_ratings_total < b.user_ratings_total) return 1;
        return 0;
      });

      
      //placesList配列をループして、
      //結果表示のHTMLタグを組み立てる
      var resultHTML = "<ol>";
      var marker = [];
      
    
      
      for (var i = 0; i < placesList.length; i++) {
        place = placesList[i];
        //マーカー内に表示する写真のurl
        const photos = place.photos;
        const photoUrl = photos[0].getUrl({maxWidth: 200, maxHeight: 150});
        
        //ここで各place事にマーカーの処理をする
        var infoWindow = new google.maps.InfoWindow();
         marker = new google.maps.Marker({
            map: map,
            position: place.geometry.location
        });
        
         // マーカーをmarkers配列に追加
        markers.push(marker);
        
        (function(marker, place) {
        google.maps.event.addListener(marker, 'click', function() {
          var markerContent = "<strong>" + place.name + "</strong><br>" +
                        "評価: " + place.rating + "<br>" +
                        "レビュー数: " + place.user_ratings_total + "<br>" +
                        "<img src=" + photoUrl + "/>"

          infoWindow.setContent(markerContent);
          infoWindow.open(map, marker);
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
        resultHTML += "<a href=/maps/detail?lat="+ place.geometry.location.lat() +"&lng="+ place.geometry.location.lng() + "&id="+ place.place_id + "&name=" + name +">";
        resultHTML += content;
        resultHTML += "</a>";
        resultHTML += "</li>";
        
        

      }
      
      resultHTML += "</ol>";
      
      //結果表示
      document.getElementById("results").innerHTML = resultHTML;
  } else{
    // 検索失敗時
    document.getElementById("results").innerHTML = "結果が見つかりませんでした。";
  }
}


</script>
