<!DOCTYPE html>
<html lang="ja">
 
 <head>
    <meta charset="utf-8">
   <title>投稿</title>
    <!--css-->
    <link href="{{ asset('/css/create.css') }}" rel="stylesheet" />
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
    
    <form action="/posts" method="POST" enctype="multipart/form-data">
        @csrf
        
         <!-- タイトルフォーム -->
        <div class="title">
            <h2 class = "formStr">タイトル</h2>
            <input type="text" name="post[title]" value="{{old('post.title')}}" placeholder="タイトル" class = "titleForm">
            <p class="title_error">{{$errors->first('post.title')}}</p>
        </div>
        
        <!-- 名称フォーム -->
        <div class="temple">
            <h2 class = "formStr">場所名</h2>
            <input type="text" name="post[temple]" value="{{old('post.temple')}}" placeholder="場所名" class = "templeForm">
            <p class="temple_error">{{$errors->first('post.temple')}}</p>
        </div>
        
        <!-- 場所入力フォーム -->
        <div class="place">
            <label for="prefecture" class = "preCity">都道府県:</label>
            <select id="prefecture">
                <option value="">選択してください</option>
            </select>
            <p class="place_error">{{$errors->first('post_places.prefecture')}}</p>
        </div>
        
        <div class="place">
          <label for="city" class = "preCity">市区町村:</label>
            <select id="city">
                <option value="">選択してください</option>
            </select>
            <!--　保存処理用のフォーム準備　-->
            <input id = "postPrefecture" name = "post_places[prefecture]"  type = "hidden">
            <input id = "postCity" name = "post_places[city]" type = "hidden">
            <p class="place_error">{{$errors->first('post_places.city')}}</p>
        </div>
        
        <!-- カテゴリーフォーム -->
        <h2 class = "formStr">カテゴリー</h2>
        <div class="category">
            @foreach($categories as $category)
            
            <label>
                {{-- valueを'$subjectのid'に、nameを'配列名[]'に --}}
                <input type="checkbox" value="{{ $category->id }}" name="categories_array[]" class = "categoryForm">
                    {{$category->name}}
                </input>
            </label>
            
        @endforeach 
            
        
        </div>
        <!-- コメントフォーム -->
        <div class="comment">
            <h2 class = "formStr">コメント</h2>
            <textarea name="post[comment]" placeholder="コメントを入力してください" class = "commentForm">{{old('post.comment')}}</textarea>
            <p class="comment_error">{{$errors->first('post.comment')}}</p>
        </div>
        
        <!-- 写真フォーム -->
        <div class="image">
            <h2 class = "formStr">写真</h2>
            <p class = "imageForm"><input type="file" name="image" >
            <br>サイズは1700KBまで</p>
            <p class="photo_error">{{$errors->first('post.photo')}}</p>
        </div>
        
        <!-- 送信用ボタン -->
        <input type="submit" class = "submit">
    </form>    
    <script>
        let prefectureSelect = document.getElementById('prefecture');
        let citySelect = document.getElementById('city');
        
        // 都道府県及び市区町村のデータを使用する
        fetch('https://japanese-addresses-v2.geoloniamaps.com/api/ja.json')
                    .then(response => response.json())
                    .then(data => {
                        // データの中から都道府県名を抽出
                        let allDates = data.data;
                        
                          allDates.forEach((data) => {
                          const option = document.createElement('option');
                          option.value = data.code;
                          option.textContent = data.pref;
                          prefectureSelect.appendChild(option);
                        });
                        
                        
                        // 都道府県が選択されたときに市区町村を取得するイベントリスナーを追加
                        prefectureSelect.addEventListener('change', function() {
                            clearCities();
                            const prefCode = this.value;
                            let cities = [];
                            allDates.forEach((data) =>{
                              if(data.code == prefCode){
                                cities = data.cities;
                              }
                            });
                            
                            cities.forEach((city) =>{
                              const option = document.createElement('option');
                              option.value = city.code;
                              let textContent = city.city;
                              if(city.ward){
                                textContent = textContent + city.ward;
                              }
                              option.textContent = textContent;
                              citySelect.appendChild(option);
                            });
                        });
        
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                    
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
</body>