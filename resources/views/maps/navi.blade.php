<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Navitime API</title>
</head>
<body>
    <div id = "templeName"></div>
    
    <div name = "favoritePlace">
        @auth
            <form action="/maps" method="POST">
                @csrf
                <input type = "hidden"  name="favoritePlace[name]" value ="{{ request()->query('name')}}">
                <input type = "hidden"  name="favoritePlace[place_id]" value ="{{ request()->query('id') }}">
                <input type = "hidden"  name="favoritePlace[latitude]" value ="{{ request()->query('lat') }}">
                <input type = "hidden"  name="favoritePlace[longitude]" value ="{{ request()->query('lng') }}">
                <input type="submit" value="地点登録">
            </form>
        @endauth
    </div>
    
    <h1>公共交通機関検索</h1>
    <form>
        <label for="time">日付と時刻:</label>
        <input type="datetime-local" id="time" name="datetime" >
        <label for = "start">start:</lavel>
        <input  id = "startLatLng" type = "hidden">
        <input id = "start" type = "text" >
         <label for = "goal">goal:</lavel>
        <input id = "goal" type = "text">
        <input  id = "goalLatLng" type = "hidden">
        <input type="button" value="検索" onclick = "geoCode();">
    </form>
    <div id="result"></div>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=firstLoad" async defer></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const templeName = urlParams.get('name');
    const templeLat = parseFloat(urlParams.get('lat'));
    const templeLng = parseFloat(urlParams.get('lng'));
    const apiUrl = 'https://navitime-route-totalnavi.p.rapidapi.com/route_transit?';
    const options = {
	method: 'GET',
	headers: {
		'x-rapidapi-key': '{{ config("services.navitime.apikey") }}',
		'x-rapidapi-host': 'navitime-route-totalnavi.p.rapidapi.com'
    	}
    };
    
    
    function firstLoad(){
        //前ページからの引き継ぎがある場合の処理
        document.getElementById('templeName').innerHTML = `<h1>${templeName}</h1>`;
        document.getElementById('goal').value = templeName;
        document.getElementById('goalLatLng').value = `${templeLat},${templeLng}`;
        //現在地取得
        navigator.geolocation.getCurrentPosition(function(position) {
            // 緯度・経度を変数に格納
            let currentLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            document.getElementById('startLatLng').value = `${currentLatLng.lat()},${currentLatLng.lng()}`;
            document.getElementById('start').value = "現在地";
        },
        // 位置情報の取得に失敗した場合
        function(error) {
            console.error("位置情報の取得に失敗しました: " + error.message);
        });
        // autocompleteの記述 
        //スタート地点
        let start = document.getElementById("start");
        startAutocomplete = new google.maps.places.Autocomplete(start);
        startAutocomplete.addListener('place_changed', function() {
            const startPlace = startAutocomplete.getPlace();
            if (startPlace.geometry) {
                // 位置が取得できた場合
                var location = startPlace.geometry.location;
                var startAddressLat = parseFloat(location.lat());
                var startAddressLng = parseFloat(location.lng());
                document.getElementById("startLatLng").value = `${startAddressLat},${startAddressLng}`;
            } else {
                alert("場所が見つかりませんでした。");
            }
        });
        //ゴール地点
        let goal = document.getElementById("goal");
        goalAutocomplete = new google.maps.places.Autocomplete(goal);
        goalAutocomplete.addListener('place_changed', function() {
            const goalPlace = goalAutocomplete.getPlace();
            if (goalPlace.geometry) {
                // 位置が取得できた場合
                var location = goalPlace.geometry.location;
                var goalAddressLat = parseFloat(location.lat());
                var goalAddressLng = parseFloat(location.lng());
                document.getElementById("goalLatLng").value = `${goalAddressLat},${goalAddressLng}`;
            } else {
                alert("場所が見つかりませんでした。");
            }
        });
    }
    
    //type ='hidden'になっているinput要素を制御するための関数
    document.addEventListener('DOMContentLoaded', function() {
        // idが1のinput要素を取得
        let startElement = document.getElementById("start");
        let goalElement = document.getElementById("goal");
    
        // inputイベントリスナーを追加
        startElement.addEventListener('input', function() {
            // startのvalueが空かどうかを確認
            if(startElement.value === '') {
                // valueが空の場合srartLatLngも空にする
                document.getElementById('startLatLng').value = "";
            }
        });
        
        goalElement.addEventListener('input', function() {
            // goalのvalueが空かどうかを確認
            if(goalElement.value === '') {
                // valueが空の場合goalLatLngも空にする
                document.getElementById('goalLatLng').value = "";
            }
        });
    });
    
    
    //クリックされた時に実行される関数
    function geoCode(){
        let startAdress = document.getElementById('start').value;
        let goalAdress = document.getElementById('goal').value;
        let time = document.getElementById('time').value;
        let startLatLng = document.getElementById('startLatLng').value;
        let goalLatLng = document.getElementById('goalLatLng').value;
        
        if(startAdress&&goalAdress&&time){
            let geocoder = new google.maps.Geocoder();
            //start,goalともに座標がわかっていない場合（hiddenに値が入っていない場合）
            if(!startLatLng&&!goalLatLng){
                //スタート地点のジオコーディング
                geocoder.geocode({
                  address: startAdress
                },
                function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {
                    let startLocation = results[0].geometry.location;
                    let startLocationLatLng = `${startLocation.lat()},${startLocation.lng()}`;
                    //ゴール地点のジオコーディング
                    geocoder.geocode({
                      address: goalAdress
                    },
                    function(results, status) {
                      if (status == google.maps.GeocoderStatus.OK) {
                        let goalLocation = results[0].geometry.location;
                        let goalLocationLatLng = `${goalLocation.lat()},${goalLocation.lng()}`;
                        routeSearch(startLocationLatLng,goalLocationLatLng);
                      }
                      else {
                        alert("位置情報が取得できませんでした。");
                      }
                    });    
                  }
                  else {
                    alert("位置情報が取得できませんでした。");
                  }
                });
            //スタートのみ座標がわかっている場合   
            }else if(!goalLatLng){
                //ゴール地点のジオコーディング
                geocoder.geocode({
                    //goalAdressはgoalのvalue
                  　address: goalAdress
                },
                function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {
                    let goalLocation = results[0].geometry.location;
                    let goalLocationLatLng = `${goalLocation.lat()},${goalLocation.lng()}`;
                    routeSearch(startLatLng,goalLocationLatLng);    
                  }else {
                    alert("位置情報が取得できませんでした。");
                  }
                });
            //ゴールのみ座標がわかっている場合
            }else if(!startLatLng){
                //スタート地点のジオコーディング
                geocoder.geocode({
                   // startAdressはstartのvalue
                   address: startAdress
                },
                function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {
                    let startLocation = results[0].geometry.location;
                    let startLocationLatLng = `${startLocation.lat()},${startLocation.lng()}`;
                    routeSearch(startLocationLatLng,goalLatLng);
                  }
                  else {
                    alert("位置情報が取得できませんでした。");
                  }
                });    
            }else if(startLatLng&&goalLatLng){
                routeSearch(startLatLng,goalLatLng);
            }
        }
        
    }
  
    //経路表示用関数
    function routeSearch(startLatLng,goalLatLng){
        let time = document.getElementById('time').value;
         document.getElementById("result").innerHTML = "";
        let requestUrl = `${apiUrl}start=${startLatLng}&goal=${goalLatLng}&start_time=${time}`;
    
        // 日時のフォーマット関数
        function formatDate(dateString) {
            let date = new Date(dateString);
        
            // 月、日、時間、分を取得
            let month = date.getMonth() + 1; // JavaScriptでは月が0から始まるため +1
            let day = date.getDate();
            let hours = date.getHours();
            let minutes = date.getMinutes();
        
            // 分が一桁の場合は0埋め
            minutes = minutes < 10 ? '0' + minutes : minutes;
        
            // フォーマットした文字列を返す
            return `${month}月${day}日${hours}時${minutes}分`;
        }
        
        fetch(requestUrl,options)
        .then(response =>{
            return response.json();
        })
    
        .then(data => {
            // 最初の経路候補を取得
            let route = data.items[0];
            let resultHTML = "";
        
            for(let i = 0; i < data.items.length; i++) {
                let route = data.items[i];
                
                resultHTML += `<h3>${i+1}</h3>`;
                
                // 合計の呼び出し
                if(route.summary){
                    let totalTime = route.summary.move.time; // 合計でかかる時間
                    let totalTime_hour = Math.floor(totalTime / 60); // 時間部分
                    let totalTime_minute = totalTime % 60; // 分部分
                    let revision_totalTime = totalTime_hour > 0 ? `${totalTime_hour}時間 ${totalTime_minute}分` : `${totalTime_minute}分`;
                    resultHTML += `<h4>${revision_totalTime}</h4>`;
                    if(route.summary.move && route.summary.move.fare && route.summary.move.fare.unit_0){
                        resultHTML += `<h4>${route.summary.move.fare.unit_0}円</h4>`; // 合計の運賃
                    }
                    resultHTML += "<hr>";
                }
                
                // 出発・到着時刻のフォーマットを適用
                resultHTML += `<h4>出発時刻 ${formatDate(route.summary.move.from_time)}</h4>`;
                resultHTML += `<h4>到着時刻 ${formatDate(route.summary.move.to_time)}</h4>`;
                
                // section要素をループさせる
                route.sections.forEach(function(section) {
                    let type = section.type;
                    if(type === "move"){
                        //距離の処理
                        let distance = parseFloat(section.distance);
                        let km = Math.floor(distance / 1000); // キロメートル部分
                        let m = distance % 1000; // メートル部分
                        //時間の処理
                        let time = parseFloat(section.time);
                        let time_hour = Math.floor(time / 60); // 時間部分
                        let time_minute = time % 60; // 分部分
                        //移動手段
                        if(section.move === "superexpress_train"){
                            resultHTML += `<p>移動手段 新幹線</p>`;    
                        }
                        if(section.move === "local_train"){
                            resultHTML += `<p>移動手段 電車</p>`;    
                        }
                        if(section.move === "rapid_train"){
                            resultHTML += `<p>移動手段 急行電車</p>`;    
                        }
                        //移動方法
                        resultHTML += `<p>移動方法 ${section.line_name}</p>`;
                        //出発時刻
                        resultHTML += `<p>出発時間 ${formatDate(section.from_time)}</p>`;
                        //到達時刻
                        resultHTML += `<p>到達時間 ${formatDate(section.to_time)}</p>`;
                        //距離
                        let revisionDistance = km > 0 ? `${km}km ${m}m` : `${m}m`;
                        resultHTML += `<p>距離 ${revisionDistance}</p>`;
                        let revision_time = time_hour > 0 ? `${time_hour}時間 ${time_minute}分` : `${time_minute}分`;
                        resultHTML += `<p>移動時間 ${revision_time}</p>`;
                        //電車を使用していた場合の料金表記
                        if(section.move === "superexpress_train" || section.move === "local_train"||section.move === "rapid_train"){
                            if(section.transport.fare){
                                if(section.transport.fare.unit_0){
                                    resultHTML += `<p>料金 ${section.transport.fare.unit_0}円</p>`;
                                }else if(section.transport.fare.unit_1){
                                    resultHTML += `<p>料金 ${section.transport.fare.unit_1}円</p>`;
                                }
                            }
                        }
                    } else if(type === "point"){
                        resultHTML += `<p>場所名 ${section.name}</p>`;
                    }
        
                    resultHTML += "<hr>";
        })
    }

        
        document.getElementById("result").innerHTML = resultHTML;
    
        })
        .catch(error => {
            console.error('エラーが発生しました:', error);
            document.getElementById('result').innerHTML = '<p>経路情報の取得に失敗しました。</p>';
        });
    }

        
    </script>
</body>
</html>