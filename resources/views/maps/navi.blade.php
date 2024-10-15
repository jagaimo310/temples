<!DOCTYPE html>
<html lang="ja">
<head>
    <!--AJAXを使用するためのタグ-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>公共交通機関経路検索</title>
    <!--css-->
    <link href="{{ asset('/css/navi.css') }}" rel="stylesheet" />
</head>
<body>
    <!--ヘッダー-->
    <div class=header>
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
    
    
    <form>
        <label for="time" class = "time">
            <select id = "startGoal" class = "startGoal" >
                <option value = "start">出発時刻</option>
                <option value = "goal">到着時刻</option>
            </select>
        </label>
        <input type="datetime-local" id="time" name="datetime" class = "datetime">
        <div class = "startD">
            <input id = "start" type = "text" class = "start">
            
            <!--ログイン時にお気に入り地点を表示する-->
            @auth
            <div class = "startDropdown" id="startDropdown" >
                @foreach($favoritePlaces as $favoritePlace)
                    <!--data-に値をセットするときはハイフンを入れる様にすること　また、javascriptで呼び出すときはキャメルケースにしなければならない　今回はplaceId-->
                    <div data-start-latLng="{{$favoritePlace->latitude}},{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div> 
                @endforeach
            </div>
            @endauth
        </div>
        <input  id = "startLatLng" type = "hidden" >
        <div id = "places" class = places></div>
        <div class = "goalD">
            <input id = "goal" type = "text" class = "goal">
            
            <!--ログイン時にお気に入り地点を表示する-->
            @auth
                <div id="goalDropdown" class = "goalDropdown">
                    @foreach($favoritePlaces as $favoritePlace)
                        <div data-goal-latLng="{{$favoritePlace->latitude}},{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div>
                    @endforeach
                </div>
            @endauth
        </div>
        
        <input type="button" value="地点入れ替え" onclick = "alterPlace();" class = "alter">
        <div class = "add">
            <button type = 'button' onclick = "clickAdd();" class = "clickAdd">地点追加</button>
            <button type = 'button' onclick = "clickDelete();" class = "clickDelete">地点削除</button>
        </div>
        
        <input  id = "goalLatLng" type = "hidden" >
        <input type="button" value="検索" onclick = "geoCode();" class = "get">
        
    </form>
    
    <div id="mapArea" class = "mapArea"></div>
    <div id = "save">
        <div id="result" class = "result"></div>
    </div>
    <!--ルート保存用-->
    <div id = "routeButton" class = "routeButton"></div>
    
<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=firstLoad" defer></script>
<script>
    //値点数管理用
    let clickCount = 0;
    //マーカー管理　リセットするためにグローバルスコープにする　関数内だと新しい関数扱いでリセットされない
    let markers = [];
    const urlParams = new URLSearchParams(window.location.search);
    const templeName = urlParams.get('name');
    const templeLat = parseFloat(urlParams.get('lat'));
    const templeLng = parseFloat(urlParams.get('lng'));
    const options = {
                        method: 'GET',
                        headers: {
                        	'x-rapidapi-key': '{{ config("services.navitime.apikey") }}',
                        	'x-rapidapi-host': 'navitime-route-totalnavi.p.rapidapi.com'
                        	}
                    };
    
    function firstLoad(){
        //初期マップ
        map = new google.maps.Map(document.getElementById("mapArea"), {
            zoom: 5,
            center: new google.maps.LatLng(36,138),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        
        //前ページからの引き継ぎがある場合の処理
        document.getElementById('goal').value = templeName;
        if(templeLat&&templeLng){
            document.getElementById('goalLatLng').value = `${templeLat},${templeLng}`;
        }
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
    
    //html制御用
    document.addEventListener('DOMContentLoaded', function() {
        // input要素を取得
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
        
        @auth
            //ドロップダウン用
            let start = document.getElementById('start');
            let startDropdown = document.getElementById('startDropdown');
            let goal = document.getElementById('goal');
            let goalDropdown = document.getElementById('goalDropdown');
    
            // ドロップダウンを表示
            //start
            start.addEventListener('focus', function() {
                startDropdown.style.display = 'block';
            });
            
            //goal
            goal.addEventListener('focus', function() {
                goalDropdown.style.display = 'block';
            });
    
            // ドロップダウンのアイテムがクリックされた時の処理
            //start
            startDropdown.addEventListener('click', function(event) {
                if (event.target && event.target.matches('div')) {
                    //eventはクリック、targetはそれが実行された位置
                    start.value = event.target.textContent;
                    startDropdown.style.display = 'none';
                    //htmlのdata-はjavacriptではdataset.〜で取得する。またハイフンはキャメルケースで書き直すこと
                    document.getElementById('startLatLng').value = event.target.dataset.startLatlng;
                }
            });
            
            //goal
            goalDropdown.addEventListener('click', function(event) {
                if (event.target && event.target.matches('div')) {
                    goal.value = event.target.textContent;
                    goalDropdown.style.display = 'none';
                    document.getElementById('goalLatLng').value = event.target.dataset.goalLatlng;
                }
            });
    
            // ドロップダウン以外をクリックするとドロップダウンを非表示にする
            //start
            document.addEventListener('click', function(event) {
                if (!start.contains(event.target) && !startDropdown.contains(event.target)) {
                    startDropdown.style.display = 'none';
                }
            });
            
            //goal
            document.addEventListener('click', function(event) {
                if (!goal.contains(event.target) && !goalDropdown.contains(event.target)) {
                    goalDropdown.style.display = 'none';
                }
            });
        @endauth
        
        startDropdown.style.display = 'none';
        goalDropdown.style.display = 'none';
    });
    
    
    //中間地点での処理 クリックしたときに同時に発動させるとまだできていないHTML要素の定義することになってしまうため関数を分ける
    function addDrop(clickCount){
        let addPoint = document.getElementById(`add[${clickCount}]`);
        let addDropdown = document.getElementById(`addDropdown[${clickCount}]`);
        // ドロップダウンを表示
        addPoint.addEventListener('focus', function() {
            addDropdown.style.display = 'block';
        });
        
        // ドロップダウンのアイテムがクリックされた時の処理
        addDropdown.addEventListener('click', function(event) {
            if (event.target && event.target.matches('div')) {
                //eventはクリック、targetはそれが実行された位置
                addPoint.value = event.target.textContent;
                addDropdown.style.display = 'none';
                //htmlのdata-はjavacriptでは変数が含まれるときはdataset[`${}`]で取得する。またキャメルケースにも変更する
                document.getElementById(`addLat[${clickCount}]`).value = event.target.dataset[`${clickCount}Lat`];
                document.getElementById(`addLng[${clickCount}]`).value = event.target.dataset[`${clickCount}Lng`];
            }
        });
        
        // ドロップダウン以外をクリックするとドロップダウンを非表示にする
        document.addEventListener('click', function(event) {
            if (!addPoint.contains(event.target) && !addDropdown.contains(event.target)) {
                addDropdown.style.display = 'none';
            }
        });
        
        //hidden管理用
        addPoint.addEventListener('input', function() {
            // startのvalueが空かどうかを確認
            if(addPoint.value === '') {
                // valueが空の場合srartLatLngも空にする
                document.getElementById(`addLat[${clickCount}]`).value = "";
                document.getElementById(`addLng[${clickCount}]`).value = "";
            }
        });
    }
    
    //地点入れ替え用関数
    function alterPlace(){
        let startAdress = document.getElementById('start').value;
        let goalAdress = document.getElementById('goal').value;
        let startLatLng = document.getElementById('startLatLng').value;
        let goalLatLng = document.getElementById('goalLatLng').value;
        document.getElementById('start').value = goalAdress;
        document.getElementById('goal').value = startAdress;
        document.getElementById('startLatLng').value = goalLatLng;
        document.getElementById('goalLatLng').value = startLatLng;
    }
    
    //クリックされた時に実行される関数
    async function geoCode(){
        document.getElementById("result").innerHTML = "";
        //promisesを配列に収納できるように準備
        let promises = [];
        let startAdress = document.getElementById('start').value;
        let goalAdress = document.getElementById('goal').value;
        let time = document.getElementById('time').value;
        let startLatLng = document.getElementById('startLatLng').value;
        let goalLatLng = document.getElementById('goalLatLng').value;
        //中間地点情報用の配列準備
        let addValues = [];
        let addLats = [];
        let addLngs = [];
        let addTimes = [];
        //中間地点のすべての値を取得
        for(let i = 0; i < clickCount; i++){
            addValues[i] = document.getElementById(`add[${i}]`).value;
            addLats[i] = document.getElementById(`addLat[${i}]`).value;
            addLats[i] = document.getElementById(`addLng[${i}]`).value;
            addTimes[i] = document.getElementById(`addTime[${i}]`).value;
        }

        //値が入っているかチェックするための関数
        let checkPrepareAddValues = addValues.every(item => item !== null && item !== undefined && item !== '');
        
        
        if(!startAdress||!goalAdress||!time||!checkPrepareAddValues){
            return;   
        }
        let geocoder = new google.maps.Geocoder();
        
        //startIdがない場合
        if(!startLatLng){
            //処理を終わらせてから次の関数を実行したいのでpromiseを使用
            promises.push(
                new Promise(function(resolve, reject) {
                    geocoder.geocode({
                        address: startAdress
                      },
                      function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            resolve({
                                    lat:results[0].geometry.location.lat(),
                                    lng:results[0].geometry.location.lng()
                                });
                        }else {
                          reject(start + "：位置情報が取得できませんでした。");
                        }
                     }
                    );
                    //resolveで得た値はresultに入る
                }).then(function(result) {
                    document.getElementById("startLatLng").value = `${result.lat},${result.lng}`;
                    //rejectで得た値はerrorに入る
                }).catch(function(error) {
                    alert(error);
                })
            );
        }
        
        //goalIdがない場合
        if(!goalLatLng){
            promises.push(
                //処理を終わらせてから次の関数を実行したいのでpromiseを使用
                new Promise(function(resolve, reject) {
                    geocoder.geocode({
                        address: goalAdress
                      },
                      function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            resolve({
                                    lat:results[0].geometry.location.lat(),
                                    lng:results[0].geometry.location.lng()
                                });
                        }else{
                            reject(addressInput + "：位置情報が取得できませんでした。");
                        }
                     }
                    );
                }).then(function(result){
                    document.getElementById("goalLatLng").value = `${result.lat},${result.lng}`;
                }).catch(function(error){
                    alert(error);
                })
            );
        }    
                
        //中間地点の全部のidに値をいれる
        for(let i = 0; i < clickCount; i++){
            if(!addLats[i]||!addLngs[i]){
                //処理を終わらせてから次の関数を実行したいのでpromiseを使用
                promises.push(
                    new Promise(function(resolve,reject){
                        //ジオコーディングして全部のplace_idを取得
                        geocoder.geocode({
                            address: addValues[i]
                          },
                          function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                resolve({
                                    lat:results[0].geometry.location.lat(),
                                    lng:results[0].geometry.location.lng()
                                });
                            }
                            else {
                              reject("中間地点の位置情報が取得できませんでした。");
                            }
                          }
                        );
                    }).then(function(result){
                        //次の関数に渡すために用意
                        addLats[i] = result.lat;
                        addLngs[i] = result.lng;
                        document.getElementById(`addLat[${i}]`).value = result.lat;
                        document.getElementById(`addLng[${i}]`).value = result.lng;
                    }).catch(function(error){
                        alert(error);
                    })
                );
            }
        }
        
        //promiseの処理が終わってから次の関数の処理を行う
        try {
            await Promise.all(promises);
            routeSearch(addLats,addLngs,addTimes);
        } catch (error) {
            console.error("エラーが発生しました:", error);
        }
        
        
    }
  
    //経路表示用関数
    function routeSearch(addLats,addLngs,addTimes){
        document.getElementById("result").innerHTML = "Now Loading...";
        //スクロール設定
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth'
        });
        let startLatLng = document.getElementById('startLatLng').value;
        let goalLatLng = document.getElementById('goalLatLng').value;
        let startGoal = document.getElementById('startGoal').value;
        let time = document.getElementById('time').value;
        const apiUrl = 'https://navitime-route-totalnavi.p.rapidapi.com/route_transit?';
        let requestUrl;
        let setTime;
        //指定が出発時刻だった場合
        if(startGoal === "start"){
            setTime = "start_time";
        }else{
        //指定が到着時刻だった場合
            setTime = "goal_time";
        }
        
        
        if(addLats.length > 0 || addLngs.length > 0 || addTimes.length > 0){
             //中間地点をurlにいれる形にする
             let via = [];
            for(let i = 0 ;i<clickCount ;i++){
                via.push({"lat": addLats[i],"lon": addLngs[i],"stay-time":addTimes[i]});
            }
            requestUrl = `${apiUrl}start=${startLatLng}&goal=${goalLatLng}&${setTime}=${time}&via=${encodeURIComponent(JSON.stringify(via))}&via_type=optimal&shape=true&shape_color=railway_line`;
        }else{
            //中間地点がなかった場合
            requestUrl = `${apiUrl}start=${startLatLng}&goal=${goalLatLng}&${setTime}=${time}&shape=true&shape_color=railway_line`;
        }
        
        
        fetch(requestUrl,options)
        .then(response =>{
            return response.json();
        })
    
        .then(data => {
            // 最初の経路候補を取得
            console.log(data.items);
            let resultHTML = "";
            //地図情報リセット
            map.data.setStyle(function() {
                return null; 
            });
            map.data.forEach(function(feature) {
                map.data.remove(feature);
            });
            
            //データを地図に追加
            map.data.addGeoJson(data.items[0].shapes);
            
            // スタイルの設定
            map.data.setStyle(function(feature){
                const inline = feature.getProperty('inline');
                return {
                    strokeColor: inline.color,
                    strokeWeight: inline.width,
                    strokeOpacity: inline.opacity,
                    strokeLineCap: inline.strokelinecap,
                    strokeLineJoin: inline.strokelinejoin,
                };
            });
            
            
            // マーカーリセット
            if (markers.length > 0) {  
                for (let i = 0; i < markers.length; i++) {
                    markers[i].setMap(null);
                }
            }
            
            
            let route = data.items[0];
            // 出発・到着時刻のフォーマットを適用
            resultHTML += `<h3>${formatDate(route.summary.move.from_time)}➡${formatDate(route.summary.move.to_time)}<h3>`;
            // 合計の呼び出し
            if(route.summary){
                let totalTime = route.summary.move.time; // 合計でかかる時間
                let totalTime_hour = Math.floor(totalTime / 60); // 時間部分
                let totalTime_minute = totalTime % 60; // 分部分
                let revision_totalTime = totalTime_hour > 0 ? `${totalTime_hour}時間 ${totalTime_minute}分` : `${totalTime_minute}分`;
                resultHTML += `<h4>(${revision_totalTime})</h4>`;
                if(route.summary.move && route.summary.move.fare && route.summary.move.fare.unit_0){
                    resultHTML += `<h4>¥${route.summary.move.fare.unit_0.toLocaleString()}</h4>`; // 合計の運賃
                }
            }
            
            
            resultHTML +=　`<hr>`;
            // section要素をループさせる
            route.sections.forEach(function(section) {
                let type = section.type;
                if(type === "move"){
                    //時間の処理
                    let time = parseFloat(section.time);
                    let time_hour = Math.floor(time / 60); // 時間部分
                    let time_minute = time % 60; // 分部分
                    
                    //出発時刻
                    resultHTML += `<h4>${formatDate(section.from_time)}発</h4>`;
                    
                    //移動手段
                    if(section.move === "superexpress_train"){
                        resultHTML += `<h4>新幹線(${section.line_name})</h4>`;    
                    }else if(section.move === "local_train"||section.move === "rapid_train"){
                        resultHTML += `<h4>電車(${section.line_name})</h4>`;    
                    }else{
                    //移動方法
                    resultHTML += `<h4>${section.line_name}</h4>`;
                    }
                    
                    let revisionTime = time_hour > 0 ? `${time_hour}時間 ${time_minute}分` : `${time_minute}分`;
                    //電車を使用していた場合の料金表記
                    if(section.move === "superexpress_train" || section.move === "local_train"||section.move === "rapid_train"){
                        if(section.transport.fare){
                            if(section.transport.fare.unit_0){
                                resultHTML += `<p>${revisionTime}   ${section.transport.fare.unit_0.toLocaleString()}円</p>`;
                            }else if(section.transport.fare.unit_1){
                                resultHTML += `<p>${revisionTime}   ${section.transport.fare.unit_1.toLocaleString()}円</p>`;
                            }
                        }
                    //歩きの場合に距離表示
                    }else if(section.move === "walk"){
                        resultHTML += `<p>${revisionTime}   ${section.distance.toLocaleString()}m</p>`;
                    }else{
                        resultHTML += `<p>${revisionTime}</p>`;
                    }
                    
                    
                    //到達時刻
                    resultHTML += `<h4>${formatDate(section.to_time)}着</h4>`;
                    
                } else if(type === "point"){
                    if(Array.isArray(section.node_types)&&section.node_types.includes("station")){
                        resultHTML += `<h3> ${section.name}駅</h3>`;
                    }else if(section.name === 'start'){
                        resultHTML += `<h3>${document.getElementById('start').value}</h3>`;
                        //マーカーをセット
                        let marker = new google.maps.Marker({
                            position: { lat: section.coord.lat, lng: section.coord.lon }, 
                            map: map,
                            title: '出発地点',
                        });
                        markers.push(marker);
                    }else if(section.name === 'goal'){
                        resultHTML += `<h3>${document.getElementById('goal').value}</h3>`;
                        //マーカーをセット
                        let marker = new google.maps.Marker({
                            position: { lat: section.coord.lat, lng: section.coord.lon }, 
                            map: map,
                            title: '到着地点', 
                        });
                        markers.push(marker);
                        
                    }else if(section.name === "経由地"){
                        //経由地の名前を取得
                        let addValues = []; 
                        for(let i = 0; i < clickCount; i++){
                            addValues[i] = document.getElementById(`add[${i}]`).value;
                        }
                        
                        //絶対値が0.00001以下であることを確かめる関数
                        function nearyEqual(add,section) {
                            //Math.absは絶対値を確認する
                            return Math.abs(add - section) < 0.00001;
                        }
                        
                        //緯度経度が一致する経由地を載せる
                        for(let i = 0; i < clickCount; i++){
                            if(nearyEqual(addLats[i],section.coord.lat) && nearyEqual(addLngs[i],section.coord.lon)){
                                resultHTML += `<h3> ${addValues[i]}</h3>`;
                            }
                        }
                        
                        //滞在時間を表示
                        resultHTML += `<h4> 滞在時間${section.stay_time}分</h4>`;
                        //マーカーをセット
                        let marker = new google.maps.Marker({
                            position: { lat: section.coord.lat, lng: section.coord.lon }, 
                            map: map,
                            title: '中間地点', 
                        });
                        markers.push(marker);
                    }else{
                         resultHTML += `<h3> ${section.name}</h3>`;
                    }
                    
                }
                resultHTML += "<hr>";
            })
            
        
        document.getElementById("result").innerHTML = resultHTML;
        document.getElementById("routeButton").innerHTML = `@auth<button onclick = "savePage();" class = "savePage">ルート保存</button>@endauth`;
        })
        .catch(error => {
            console.error('エラーが発生しました:', error);
            document.getElementById('result').innerHTML = '<p>経路情報の取得に失敗しました。</p>';
        });
    }
    
    // 日時のフォーマット関数
    function formatDate(dayTime) {
        let date = new Date(dayTime);
    
        // 月、日、時間、分を取得
        let hours = date.getHours();
        let minutes = date.getMinutes();
    
        // 分が一桁の場合は0埋め
        minutes = minutes < 10 ? '0' + minutes : minutes;
    
        // フォーマットした文字列を返す
        return `${hours}:${minutes}`;
    }

     
     //追加ボタンが押されたときの処理
    function clickAdd(){
        if(clickCount<8){
            //時間用のoptionを準備　外で用意が必須
            let options = ''; 
            for (let i = 0; i <= 120; i++) {
                options += `<option value="${i}">${i}分</option>`;
            }
            
            //入力地点を要素をリセットせずに増やす
            document.getElementById("places").insertAdjacentHTML('beforeend', 
                    `<div id ='place[${clickCount}]'>
                    <div class ='addTime'>
                        滞在時間:<select id="addTime[${clickCount}]" >
                            ${options}
                        </select>
                    </div>
                    <div class = 'addD'>
                        <input class = 'addText' id='add[${clickCount}]' type='text' >
                        @auth
                            <div id="addDropdown[${clickCount}]" class = "addDropdown">
                                @foreach($favoritePlaces as $favoritePlace)
                                    <div data-${clickCount}-lat="{{$favoritePlace->latitude}}" data-${clickCount}-lng="{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div>
                                @endforeach
                            </div>
                        @endauth
                    </div>
                    <input id='addLat[${clickCount}]' type = "hidden" >
                    <input id='addLng[${clickCount}]' type = "hidden" ></br>
                    </div>`
            );
            
            //中間地点のオートコンプリートをセット　すべてのadd[clickCount]において常時発動させる必要があるのでループ処理で適応（ループ処理しないと最新のinputにしか適応されない）
            //オートコンプリートを配列にするための関数 これなしだとあとから追加されたinputの処理が優先されそれ以前の値が更新されなくなる
            let addAutocomplete = [];
            for (let i = 0; i <= clickCount; i++) {
                let add = document.getElementById(`add[${i}]`);
                addAutocomplete[i] = new google.maps.places.Autocomplete(add);
                addAutocomplete[i].addListener('place_changed', function() {
                    let addInfo = addAutocomplete[i].getPlace();
                    if (addInfo.geometry) {
                        // hiddenにplace_idをセット
                        document.getElementById(`addLat[${i}]`).value = addInfo.geometry.location.lat();
                        document.getElementById(`addLng[${i}]`).value = addInfo.geometry.location.lng();
                    } else {
                        alert("場所が見つかりませんでした。");
                    }
                });
            }
            //ログイン時ドロップダウン処理用
            @auth
                addDrop(clickCount);
            @endauth
            
            //clickCountを次回読み込まれたときのために増やす
            clickCount++;
        }else{
            confirm("中間地点の追加は8箇所までです。");
        }
    }
    
    //削除ボタンが押されたときの処理
    function clickDelete(){
        //入力地点を減らす
        if(clickCount > 0){
            clickCount--;
            document.getElementById(`place[${clickCount}]`).remove();
        }else{
            return;
        }
    }
    //保存用関数
    function savePage() {
        // 保存する要素のHTMLを取得
        let content = document.getElementById('save').innerHTML;
        let title = `${document.getElementById('start').value}➡️${document.getElementById('goal').value}`;
        // フォームデータを作成
        const formData = new FormData();
        formData.append('content', JSON.stringify({ content: content }));
        formData.append('title', title); // 通常の形式のデータを追加
        // AJAXリクエストでLaravelにデータを送信
        fetch('/saveRoute', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => {
        // リダイレクトされている場合
            if(response.redirected) {
                window.location.href = response.url;
            } 
        })
        .catch(error => {
        alert('エラーが発生しました。再度お試しください。');
    });
    }
    </script>
</body>
</html>