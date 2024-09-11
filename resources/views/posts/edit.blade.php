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
    <form action="/posts/{{ $post->id }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
         <!-- タイトルフォーム -->
        <div class="title">
            <h2>タイトル</h2>
            <input type="text" name="post[title]" value="{{$post->title}}" placeholder="タイトル">
        </div>
        
        <!-- 寺院名称フォーム -->
        <div class="temple">
            <h2>寺院名</h2>
            <input type="text" name="post[temple]" value="{{$post->temple}}" placeholder="寺院名">
        </div>
        
        <!-- 場所入力フォーム -->
        <div class="place">
            <h2>場所</h2>
            <p>{{$post->place->prefecture}} {{$post->place->area}}</p>
            <h2>変更したい場合は下記を選択</h2>
            <label for="prefecture">都道府県:</label>
            <select id="prefecture">
                <option value="">選択してください</option>
            </select>

          <label for="city">市区町村:</label>
            <select id="city">
                <option value="">選択しない</option>
            </select>
            <!--　保存処理用のフォーム準備　-->
            <input id = "postPrefecture" name = "post_places[prefecture]"  type = "hidden">
            <input id = "postCity" name = "post_places[city]" type = "hidden">
        </div>
        
        <!-- カテゴリーフォーム -->
        <div class="category">
            <h2>カテゴリー</h2>
            @foreach($post->categories as $category)   
                {{$category->name}}
            @endforeach
            <h2>変更したい場合は下記を選択</h2>
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
            <textarea name="post[comment]" placeholder="コメントを入力してください">{{$post->comment}}</textarea>
        </div>
        
        <!-- 写真フォーム -->
        <div class="image">
            <img src = "{{$post->image }}" alt = "写真">
            <h2>変更したい場合は新しい写真を選択</h2>
            <input type="file" name="image">
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
    <a href = "/posts/{{$post->id}}">戻る</a>
</body>