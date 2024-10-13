<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ピンポイント検索</title>
    <!--css-->
    <link href="{{ asset('/css/search.css') }}" rel="stylesheet" />
</head>
<body>
    <!--ヘッダー-->
    <div class="header">
        <a href="/">トップ</a>
        <a href="/register">新規登録</a>
        <a href="/posts/mypage">ログイン・マイページ</a>
        <a href="/posts/postsAll">投稿表示</a>
        <a href="/posts/create">投稿</a>
        <a href="/maps/place">地点検索</a>
        <a href="/maps/search">ピンポイント検索</a>
        <a href="/maps/severalRoute">複数地点検索</a>
        <a href="/maps/navi">公共交通機関</a>
    </div>

    <form action="/retrieval" method="POST" id="placeForm" class = "placeForm">
         @csrf
        <input type="text" id="place" name="placeName" placeholder="場所を入力" required>
        <input id="placeId" name="placeId" type="hidden">
        <input id="placeRealName" name="placeRealName" type="hidden">
        <input type="submit" value="検索" class="submit">
    </form>

    <div id="mapArea" class="mapArea"></div>
    
    <div id="searchName" class="searchName"></div>

    <!-- お気に入り地点登録用フォーム -->
    <div class="favoritePlace">
        @auth
            <form action="/maps" method="POST">
                @csrf
                <input type="hidden" name="favoritePlace[name]" id="name">
                <input type="hidden" name="favoritePlace[place_id]" id="place_id">
                <input type="hidden" name="favoritePlace[latitude]" id="latitude">
                <input type="hidden" name="favoritePlace[longitude]" id="longitude">
                <input type="hidden" name="favoritePlace[prefecture]" id="favoritePrefecture">
                <input type="hidden" name="favoritePlace[area]" id="favoriteArea">
                <div id="submit"></div>
            </form>
            @if($errors->any())
                <p class="error">すでに登録済みです</p>
            @endif
        @endauth
    </div>

    <div id="website" class="website"></div>
    <img id="photo" class="photo">
    <div id="openingHours" class="openingHours"></div>

    @if(!empty(session('answer')))
        <div class="geminiResult">
            <h3>Gemini解説</h3>
            {!! session('answer') !!}
            <hr>
        </div>
    @endif

    <!-- レビュー一覧 -->
    <div class="blogResult">
        @if(!empty(session('posts')) && session('posts')->isNotEmpty())
            <h3>アプリレビュー</h3>
            @foreach(session('posts') as $post) 
              <a href="/posts/{{$post->id}}"><h3>{{$post->title}}</h3></a>
              <p>{{$post->temple}}</p>
              <img src="{{$post->image}}" alt="写真">
              <hr>
            @endforeach
            <hr>
        @endif
        
        @if(!empty(session('message')))
            <h3>アプリレビュー</h3>
            <p>{{session('message')}}</p>
            <hr>
        @endif
        
    </div>

    <div id="review" class="mapReview"></div>
        
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google-map.apikey') }}&libraries=places&callback=initMap" defer></script>
    <script>
      
        let urlParams = new URLSearchParams(window.location.search);
        let placeName = urlParams.get('placeName');
        let placeId = urlParams.get('placeId');
        
        let map;
        let marker;
        //クエリから値を取得
        
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
                //値をhiddenに入れる
                  document.getElementById("placeId").value = placeInfo.place_id;
                  document.getElementById('placeRealName').value = placeInfo.name;
                } else {
                   alert("場所が見つかりませんでした。");
                }
                
          });
          //クエリに値があるかどうかで分岐
          if(placeId){
            detailPlace(placeId); 
          }  
        }
        
        async function getPlace(){
          let placeFormName = document.getElementById('place').value;
          let placeFormId =  document.getElementById("placeId").value;
          
          if(!placeFormName){
            return;
          }

          if(!placeFormId){
            
            //placeNameのみの場合はジオコーディング
            let geocoder = new google.maps.Geocoder();
            return new Promise(function(resolve, reject) {
              geocoder.geocode({
                 address: placeFormName
               },
              function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {
                  //hiddenに値を入れる
                  document.getElementById("placeId").value = results[0].place_id;
                  resolve();
               }else {
                  reject(placeFormName + "：位置情報が取得できませんでした。");
                }
              });
            });
            
          }
        }
        
        
        function detailPlace(placeId){
            
            let service = new google.maps.places.PlacesService(map);

            let request = {
              placeId: placeId,
              fields:['reviews','photos','address_components','name','opening_hours','url','website','geometry','place_id']
            };
            let reviewHTML = "";
            service.getDetails(request, function(place, status) {
              if (status === google.maps.places.PlacesServiceStatus.OK) {
              let reviews = place.reviews;
              console.log(place);
              //情報があれば写真とレビューを取得して並べる
              if(place.reviews){
                let reviews = place.reviews;
                reviewHTML += "<h4>googlemapレビュー</h4>";
                //reviewsにある要素をループさせる
                place.reviews.forEach(function(review) {
                  reviewHTML += "<p>評価" + review.rating + "</p>";
                  reviewHTML += "<p>" + review.text + "</p>";
                  reviewHTML += "<p>" + review.relative_time_description + "</p>";
                  reviewHTML += "<hr>";
                });
              }
              document.getElementById("review").innerHTML = reviewHTML; 
              
              //名前をセット
              document.getElementById("searchName").innerHTML = `<h1><a class = "searchA" href = "${place.url}" target="_blank" rel="noopener noreferrer">${place.name}</a></h1>`;
              
              
              if(place.photos){
                //写真の表示
                let photo = place.photos;
                const photoUrl = photo[0].getUrl({maxWidth: 750, maxHeight: 600});
                document.getElementById("photo").src = photoUrl;
              }else{
                document.getElementById("photo").src = "";
              }
              
              //ウェブサイトurlをセット
              if(place.website){
                document.getElementById("website").innerHTML = `<a href = "${place.website}" target="_blank" rel="noopener noreferrer">公式サイトへ<a>`;
              }else{
                document.getElementById("website").innerHTML = "";
              }
              
              //営業時間
              let hourHTML = "";
              if(place.opening_hours){
                hourHTML += `<h4>営業時間</h4>`;
                place.opening_hours.weekday_text.forEach(function(hour){
                  hourHTML += `${hour}<br>`;
                });
              }
              document.getElementById("openingHours").innerHTML = hourHTML;
              
              //マップのピン立て、ズームを実行
              //地図情報の変更
              map.setCenter(place.geometry.location);
              map.setZoom(15);
              
              //ここで各place事にマーカーの処理をする
              let infoWindow = new google.maps.InfoWindow();
               marker = new google.maps.Marker({
                  map: map,
                  position: place.geometry.location
              });
              
              @auth
                //お気に入り地点保存用の値をセット
                document.getElementById('name').value = place.name;
                document.getElementById('place_id').value = place.place_id;
                document.getElementById('latitude').value = parseFloat(place.geometry.location.lat());
                document.getElementById('longitude').value = parseFloat(place.geometry.location.lng());
                document.getElementById('submit').innerHTML = `<textarea name = "favoritePlace[comment]" placeholder = "メモ（なくても保存可能）" class = "memo"></textarea>
                                                              <input type="submit" value="地点登録" class = "point">`;

                //県と市をセット
                place.address_components.forEach(function(component) {
                    if (component.types.includes("administrative_area_level_1")) {
                        document.getElementById("favoritePrefecture").value = component.long_name; 
                    }
                    if (component.types.includes("locality")) {
                        document.getElementById("favoriteArea").value = component.long_name; 
                    }
                });
              @endauth
              
            } else {
              console.error('レビューが取得できませんでした。');
            }
          });
        }
      
      //hiddenをコントロールする関数
      document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("placeForm").addEventListener("submit", async function(event) {
            event.preventDefault(); // フォームの送信を止める
            
            // ジオコーディングを実行
            await getPlace();
            
            // 処理が完了したらフォームを送信
            document.getElementById("placeForm").submit();
        });
        
        let placeElement = document.getElementById("place");
         // hiddenを制御
        placeElement.addEventListener('input', function() {
            // startのvalueが空かどうかを確認
            if(placeElement.value === '') {
                // valueが空の場合placeIdも空にする
                document.getElementById("placeId").value = "";
            }
        });    
         
      });
       
    </script>
</body>
</html>