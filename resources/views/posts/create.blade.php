<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 
 <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Blog create</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
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
    </div>
    
    <form action="/posts" method="POST" enctype="multipart/form-data">
        @csrf
        
         <!-- タイトルフォーム -->
        <div class="title">
            <h2>タイトル</h2>
            <input type="text" name="post[title]" value="{{old('post.title')}}" placeholder="タイトル">
            <p class="title_error" style="color:red;">{{$errors->first('post.title')}}</p>
        </div>
        
        <!-- 寺院名称フォーム -->
        <div class="temple">
            <h2>寺院名</h2>
            <input type="text" name="post[temple]" value="{{old('post.temple')}}" placeholder="寺院名">
            <p class="temple_error" style="color:red;">{{$errors->first('post.temple')}}</p>
        </div>
        
        <!-- 場所入力フォーム -->
        <div class="place">
            <label for="prefecture">都道府県:</label>
            <select id="prefecture">
                <option value="">選択してください</option>
            </select>
            <p class="place_error" style="color:red;">{{$errors->first('post_places.prefecture')}}</p>
            
          <label for="city">市区町村:</label>
            <select id="city">
                <option value="">選択しない</option>
            </select>
            <!--　保存処理用のフォーム準備　-->
            <input id = "postPrefecture" name = "post_places[prefecture]"  type = "hidden">
            <input id = "postCity" name = "post_places[city]" type = "hidden">
            <p class="place_error" style="color:red;">{{$errors->first('post_places.city')}}</p>
        </div>
        
        <!-- カテゴリーフォーム -->
        <div class="category">
            <h2>カテゴリー</h2>
            @foreach($categories as $category)
            
            <label>
                {{-- valueを'$subjectのid'に、nameを'配列名[]'に --}}
                <input type="checkbox" value="{{ $category->id }}" name="categories_array[]">
                    {{$category->name}}
                </input>
            </label>
            
        @endforeach 
            
        
        </div>
        <!-- コメントフォーム -->
        <div class="comment">
            <h2>コメント</h2>
            <textarea name="post[comment]" placeholder="コメントを入力してください">{{old('post.comment')}}</textarea>
            <p class="comment_error" style="color:red;">{{$errors->first('post.comment')}}</p>
        </div>
        
        <!-- 写真フォーム -->
        <div class="image">
            <h2>写真</h2>
            <input type="file" name="image">
            <p class="photo_error" style="color:red;">{{$errors->first('post.photo')}}</p>
        </div>
        
        <!-- 送信用ボタン -->
        <input type="submit" name="送信">
        
    <script>
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
        
        //都道府県の保存用データをphp内に挿入する
        document.getElementById('prefecture').addEventListener('change', function() {
            let bladePrefecture = document.getElementById('prefecture');
            let postPrefecture = bladePrefecture.options[bladePrefecture.selectedIndex].text;
            document.getElementById('postPrefecture').value = postPrefecture;
            console.log(postPrefecture);
        });
        
        //市区町村の保存用データをphp内に挿入する
        document.getElementById('city').addEventListener('change', function() {
            let bladeCity = document.getElementById('city');
            let postCity = bladeCity.options[bladeCity.selectedIndex].text;
            document.getElementById('postCity').value = postCity;
            console.log(postCity);
        });
    </script>
    </form>
</body>