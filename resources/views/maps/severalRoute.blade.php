<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>複数ルート検索</title>
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
    
    <form>
        <!--スタート地点用-->
        <input id = "start" type = "text">
        <!--ログイン時にお気に入り地点を表示する-->
        @auth
        <div id="startDropdown" style="display: none; position: absolute; background-color: white; z-index: 1000;">
            @foreach($favoritePlaces as $favoritePlace)
                <!--data-に値をセットするときはハイフンを入れる様にすること　また、javascriptで呼び出すときはキャメルケースにしなければならない　今回はplaceId-->
                <div data-start-lat="{{$favoritePlace->latitude}}" data-start-lng="{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div>
            @endforeach
        </div>
        @endauth
        <input  id = "startLat" type = 'hidden'>
        <input  id = "startLng" type = 'hidden'>
        
        <!--中間地点用-->
        <div id = "places"></div>
        
        <!--ゴール地点用-->
        <input id= "goal" type = "text">
        <!--ログイン時にお気に入り地点を表示する-->
        @auth
            <div id="goalDropdown" style="display: none; position: absolute; background-color: white; z-index: 1000;">
                @foreach($favoritePlaces as $favoritePlace)
                    <div data-goal-lat="{{$favoritePlace->latitude}}" data-goal-lng="{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div>
                @endforeach
            </div>
        @endauth
        <input  id = "goalLat" type = 'hidden'>
        <input  id = "goalLng" type = 'hidden'></br>
        
        <!--送信用-->
        <input type="button" value="検索" onclick="getPlaces();">
    </form>
    <button type = 'button' onclick = "clickAdd();">地点追加</button>
    <button type = 'button' onclick = "clickDelete();">地点削除</button>
    <div id="mapArea" style="width:700px; height:400px;"></div>
    <div id = "result"></div>
    
<script src="https://maps.googleapis.com/maps/api/js?key={{ config("services.google-map.apikey") }}&libraries=places&callback=initMap" async defer></script>
<script type="text/javascript">
    //入力地点カウント管理用変数 
<<<<<<< HEAD
    let = clickCount = 0;
    
     //コールバック関数
     window.initMap =  function(){
        //初期マップ
        map = new google.maps.Map(document.getElementById("mapArea"), {
            zoom: 5,
            center: new google.maps.LatLng(36,138),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        
        //オートコンプリート機能
        //スタート地点
        let start = document.getElementById("start");
        startAutocomplete = new google.maps.places.Autocomplete(start);
        startAutocomplete.addListener('place_changed', function() {
          const startInfo = startAutocomplete.getPlace();
          if (startInfo.geometry) {
              //hiddenにplace_idをセット
              console.log(startInfo.place_id);
              document.getElementById("startLat").value = startInfo.geometry.location.lat();
              document.getElementById("startLng").value = startInfo.geometry.location.lng();
            } else {
               alert("場所が見つかりませんでした。");
            }
            
        });
        
        //ゴール地点
        let goal = document.getElementById("goal");
        goalAutocomplete = new google.maps.places.Autocomplete(goal);
        goalAutocomplete.addListener('place_changed', function() {
          const goalInfo = goalAutocomplete.getPlace();
          if (goalInfo.geometry) {
              //hiddenにplace_idをセット
              document.getElementById("goalLat").value = goalInfo.geometry.location.lat();
              document.getElementById("goalLng").value = goalInfo.geometry.location.lng();
            } else {
               alert("場所が見つかりませんでした。");
            }
            
        });
    }
    

    let clickCount = 0;
    
    //html要素コントロール
    document.addEventListener('DOMContentLoaded', function() {
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
                document.getElementById('startLat').value = event.target.dataset.startLat;
                document.getElementById('startLng').value = event.target.dataset.startLng;
            }
        });
        
        //goal
        goalDropdown.addEventListener('click', function(event) {
            if (event.target && event.target.matches('div')) {
                goal.value = event.target.textContent;
                goalDropdown.style.display = 'none';
                document.getElementById('goalLat').value = event.target.dataset.goalLat;
                document.getElementById('goalLng').value = event.target.dataset.goalLng;
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
        
        //hiddenのinputを制御する
        // input要素を取得
        start.addEventListener('input', function() {
            // startのvalueが空かどうかを確認
            if(start.value === '') {
                // valueが空の場合srartLatLngも空にする
                document.getElementById('startLat').value = "";
                document.getElementById('startLng').value = "";
            }
        });
        
        goal.addEventListener('input', function() {
            // startのvalueが空かどうかを確認
            if(goal.value === '') {
                // valueが空の場合srartLatLngも空にする
                document.getElementById('goalLat').value = "";
                document.getElementById('goalLng').value = "";
            }
        });
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

   
    
    //ルート検索が押されたときの処理
    //処理すべき値が多く処理の順番付けをasyncをセットしpromiseを使用する
    async function getPlaces(){
        //  promisesを配列として準備
        let promises = [];
        let start = document.getElementById("start").value;
        let startLat = document.getElementById("startLat").value;
        let startLng = document.getElementById("startLng").value;
        let goal = document.getElementById("goal").value;
        let goalLat = document.getElementById("goalLat").value;
        let goalLng = document.getElementById("goalLng").value;
        //中間地点情報用の配列準備
        let addValues = [];
        let addLats = [];
        let addLngs = [];
        //中間地点のすべての値を取得
        for(let i = 0; i < clickCount; i++){
            addValues[i] = document.getElementById(`add[${i}]`).value;
            addLats[i] = document.getElementById(`addLat[${i}]`).value;
            addLngs[i] = document.getElementById(`addLng[${i}]`).value;
        }

        //値が入っているかチェックするための関数
        let checkPrepareAddValues = addValues.every(item => item !== null && item !== undefined && item !== '');
        //全部に場所の名前が入っているかをチェック
        if(!checkPrepareAddValues || !start || !goal){
            return;    
        }   
        //place_idがない場合にジオコーディングを行う
        let geocoder = new google.maps.Geocoder();
        
        //startIdがない場合
        if(!startLat&&!startLng){
            //処理を終わらせてから次の関数を実行したいのでpromiseを使用
            promises.push(
                new Promise(function(resolve, reject) {
                    geocoder.geocode({
                        address: start
                      },
                      function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            resolve({//渡す値が複数あるので配列にして結果を処理
                                lat: results[0].geometry.location.lat(),
                                lng: results[0].geometry.location.lng()
                            });
                        }else {
                          reject(start + "：位置情報が取得できませんでした。");
                        }
                     }
                    );
                    //resolveで得た値はresultに入る
                }).then(function(result) {
                    document.getElementById("startLat").value = result.lat;
                    document.getElementById("startLng").value = result.lng;
                    //rejectで得た値はerrorに入る
                }).catch(function(error) {
                    alert(error);
                })
            );
        }
        
        //goalIdがない場合
        if(!goalLat&&!goalLng){
            promises.push(
                //処理を終わらせてから次の関数を実行したいのでpromiseを使用
                new Promise(function(resolve, reject) {
                    geocoder.geocode({
                        address: goal
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
                    document.getElementById("goalLat").value = result.lat;
                    document.getElementById("goalLng").value = result.lng;
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
            serchRoutes(addLats,addLngs);
        } catch (error) {
            console.error("エラーが発生しました:", error);
        }
        
        
    }
    
   function serchRoutes(addLats,addLngs) {
           //DirectionsService のオブジェクトを生成
          let startLat = document.getElementById("startLat").value;
          let startLng = document.getElementById("startLng").value;
          let goalLat = document.getElementById("goalLat").value;
          let goalLng = document.getElementById("goalLng").value;
           
          let directionsService = new google.maps.DirectionsService();
        　//既にルートが表示されている場合そのルートをリセット
          if (window.directionsRenderer) {
            window.directionsRenderer.setMap(null);
        　}
        　//新しくマップ上に引くルートを定義
          window.directionsRenderer = new google.maps.DirectionsRenderer();
        　window.directionsRenderer.setMap(map);
          
          //リクエストの出発点の位置（Empire State Building 出発地点の緯度経度）
          let start = new google.maps.LatLng(startLat, startLng);  
          
          //リクエストの終着点の位置（Grand Central Station 到着地点の緯度経度）
          let end = new google.maps.LatLng(goalLat,goalLng);  
          
        // 中間地点がない場合
        if (clickCount === 0) {
            // ルートを取得するリクエスト
            let request = {
              origin: start,      // 出発地点の緯度経度
              destination: end,   // 到着地点の緯度経度
              travelMode: 'DRIVING' //トラベルモード
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
                document.getElementById("result").innerHTML =
                   "<p>所要時間: " + duration + "</p>" +
                    "<p>距離: " + distance + "</p>";
              }else{
               alert("ルート情報を取得できませんでした：" );
              }
            });
        } else {
            //リクエストに使えるように緯度経度を変換
            let waypoints = [];
            for(let i = 0 ;i<clickCount ;i++){
                waypoints.push({
                    location: new google.maps.LatLng(addLats[i], addLngs[i]),
                    stopover: true
                });
            }

            // ルートを取得するリクエスト
            let request = {
              origin: start,      
              destination: end,
              waypoints: waypoints,
              optimizeWaypoints: true,
              travelMode: 'DRIVING' 
            };
            
            //DirectionsService のオブジェクトのメソッドをセットして表示
            directionsService.route(request, function(result, status) {
              //ステータスがOKの場合、
              if (status === 'OK') {
                //取得したルート（結果：result）をセット
                directionsRenderer.setDirections(result); 
                //ルート情報を定義し表示
                const route = result.routes[0];
                const routeResult = document.getElementById('result');
                routeResult.innerHTML = "";
                console.log(route);

                // 最適化された順序の出力
                for(let i = 0; i < route.legs.length; i++){
                    routeResult.innerHTML += 
                      `${route.legs[i].start_address}<br>
                      ${route.legs[i].distance.text}<br>
                      ${route.legs[i].duration.text}<br>
                      ${route.legs[i].end_address}<br>
                      <hr>`
                }
                
              }else{
               alert("ルート情報を取得できませんでした：" );
              }
            });
            
            
            
        }
    }
    
    
    //追加ボタンが押されたときの処理
    function clickAdd(){
        if(clickCount<8){
            //入力地点を要素をリセットせずに増やす
            document.getElementById("places").insertAdjacentHTML('beforeend', 
                    `<div id ='place[${clickCount}]'>
                    <input id='add[${clickCount}]' type='text'>
                    @auth
                    <div id="addDropdown[${clickCount}]" style="display: none; position: absolute; background-color: white; z-index: 1000;">
                        @foreach($favoritePlaces as $favoritePlace)
                            <div data-${clickCount}-lat="{{$favoritePlace->latitude}}" data-${clickCount}-lng="{{$favoritePlace->longitude}}">{{$favoritePlace->name}}</div>
                        @endforeach
                    </div>
                    @endauth
                    <input id='addLat[${clickCount}]' type = 'hidden'>
                    <input id='addLng[${clickCount}]' type = 'hidden'></br>
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
            //ドロップダウン処理用
            addDrop(clickCount);
            
            //clickCountを次回読み込まれたときのために増やす
            clickCount++;
        }else{
            confirm("中間地点の追加は8箇所までです。");
        }
    }
    
    //削除ボタンが押されたときの処理
    function clickDelete(){
        //入力地点を減らす
        clickCount--;
        document.getElementById(`place[${clickCount}]`).remove();
    }
    
    
</script>
</body>
</html>
    