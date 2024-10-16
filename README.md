<h2><a href = "https://routecraft-deafc24a6bbe.herokuapp.com/">routecraft</a></h2>
<div class="header">
    <h3>概要</h3>
    <p>
        旅行をするにはどこに行くか、どうやって行くかなどの多くの要素を組み込んで計画を立てる必要がある。そのため、計画を立てるだけで多くの時間がかかり、様々なサイトを見て検討しなければならない。そこで、旅行計画を少しでも快適かつ効率的にすることを目指して、情報集めからルート構築までを１つのサイトでできるようにすることを目指して本アプリを作成した。
    </p>
    <img alt="トップ" src="https://github.com/user-attachments/assets/2e4b9d24-a773-46f1-b138-8afd62d7138c">
</div>
<hr>

<div class="top">
    <h3>トップ</h3>
    <h4>県、市内にある観光地を検索し行きたい場所を探すためのページ</h4>
    <img alt="タイトル" src="https://github.com/user-attachments/assets/a35ad188-aa0c-4c52-ae82-64fe390dcf4e">
</div>
<h4>クリック検索</h4>
<ul>
    <li>クリックすることで都道府県、市区町村を選択しての検索</li>
    <li>クリックでの検索は、１回目のクリックで県の範囲を検索、検索している県内をもう一度タップするとその市区町村内に絞って検索できる</li>
</ul>

<h4>選択検索</h4>
<ul>
    <li>県名選択で市区町村が自動的に選ばれる</li>
    <li>県のみでも検索可能</li>
    <li>keywordも指定可</li>   
</ul>

<h4>検索結果</h4>
<ul>
    <li>GoogleMapのレビューが多い上位20件が取得される</li>
    <li>クリックでその場所の詳細が確認できる</li>
</ul>
<hr>

<h3>地点詳細</h3>
<h4>地点の情報をより詳しく知るためのページ</h4>
<img alt="地点詳細１" src="">

<ul>
    <li>Google Mapの情報（写真、レビュー、営業時間）</li> 
    <li>Google Mapや、公式サイトへのリンク</li>
    <li>公共交通機関での検索ページへのリンク</li>
    <li>アプリ内の投稿</li> 
</ul><br>
<img  alt="地点詳細2" src="https://github.com/user-attachments/assets/7ad7052f-9e69-4807-9a22-106b65ba1602">
<p>車か徒歩でのルート検索、移動時間検索</p>
<br>
<img  alt="地点詳細3" src="https://github.com/user-attachments/assets/ba8e7696-6ff3-4c5f-a533-ccf02119e168">
<p>GEMINIによるその地点の説明</p> 
<hr>

<h3>投稿表示</h3>
<img  alt="投稿表示" src="https://github.com/user-attachments/assets/c28b0e97-7815-4b1a-a9fb-558ae11c3daf">
<ul>
    <li>表示項目はタイトル、地点名、写真</li>
    <li>keywordを指定して検索可能</li>
</ul>
<hr>

<h3>投稿詳細</h3>
<img alt="投稿詳細" src="https://github.com/user-attachments/assets/44535cd4-6970-4fe2-a77b-97c67c886c41">
<ul>
    <li>表示項目はタイトル、地点名、都道府県・市区町村、写真</li>
    <li>タイトルをクリックで地点詳細ページに遷移</li>
</ul>
<hr>

<h3>地点検索</h3>
<h4>行きたい場所が定まったあとに使い旅行の詳細を決めるためのページ</h4>
<img  alt="地点検索" src="https://github.com/user-attachments/assets/016be373-bb9b-4693-9205-908b49caba99">
<h4>検索概要</h4>
<ul>
    <li>指定した地点を中心に観光地やレストランを検索</li>
    <li>Keyword、検索範囲指定可</li>
    <li>読み込まれた際に現在地で検索</li>
</ul>
<h4>検索結果</h4>
<ul>
    <li>GoogleMapのレビューが多い上位20件が取得される</li>
    <li>クリックでその場所の詳細が確認できる</li>
</ul>
<hr>

<h3>ピンポイント検索</h3>
<h4>行きたい場所の情報を知るためのページ</h4>
<img alt="ピンポイント検索" src="https://github.com/user-attachments/assets/59988969-901c-4ea5-836e-2386de45e637">
<h4>詳細表示機能</h4>
<ul>
    <li>Google Mapの情報（写真、レビュー、営業時間）</li>
    <li>GEMINIによるその地点の説明</li>
    <li>アプリ内の投稿</li>
    <li>Google Mapや、公式サイトへのリンク</li>
</ul>
<hr>

<h3>複数地点検索</h3>
<h4>車か徒歩でのルート検索を行うページ</h4>
<img alt="複数地点検索１" src="https://github.com/user-attachments/assets/4d1e36c9-d5b6-4fe6-a60a-e153eb06698f">
<h4>検索方法</h4>
<ul>
    <li>車もしくは徒歩かを選択</li>
    <li>2地点間もしくは中間地点を指定しての検索</li>
    <li>地点追加ボタンで中間地点は８箇所まで追加可能</li>
    <li>地点入れ替えボタンで出発地点と到着地点を入れ替えられる</li>
</ul><br>
<img  alt="複数地点検索２" src="https://github.com/user-attachments/assets/6cc8baf7-5839-496e-8dd4-b6a8ec6edc46">
<h4>検索結果</h4>
<ul>
    <li>ルートを地図に表示</li>
    <li>上部に合計の移動時間と距離を表示</li>
    <li>中間地点は自動的に最短距離で回れるように中間地点が複数あった場合は自動的に最短で回るルートが提示される</li>
</ul>
<hr>

<h3>公共交通機関検索</h3>
<h4>公共交通機関でのルート検索を行うページ</h4>

<img alt="公共機関検索１" src="https://github.com/user-attachments/assets/3b7679a6-05ad-4ef8-8cc0-fb74c9f7a596">
<h4>検索方法</h4>
<ul>
    <li>バスを除く公共交通機関を使用したルートを検索</li>
    <li>開始時間もしくは到着時間を指定</li>
    <li>2地点間もしくは中間地点を指定しての検索</li>
    <li>地点追加ボタンで中間地点は８箇所まで追加可能</li>
    <li>地点入れ替えボタンで出発地点と到着地点を入れ替えられる</li>
</ul><br>

<img alt="交通機関検索２" src="https://github.com/user-attachments/assets/ff6622c4-d0f6-4a03-a061-c06c3b9ce145">
<h4>検索結果</h4>
<ul>
    <li>ルートを地図に表示</li>
    <li>上部に合計の移動時間、移動にかかる金額を表示</li>
    <li>中間地点は自動的に最短距離で回れるように中間地点が複数あった場合は自動的に最短で回るルートが提示される</li>
</ul>
<hr>

<p>Breezeを利用したユーザー登録後、<br><strong>投稿　投稿編集・削除　マイページ　地点お気に入り登録・共有　ルート登録・共有　</strong><br>機能が解放される。</p><br>
<img  alt="お気に入り地点" src="https://github.com/user-attachments/assets/8cb3c336-6032-4bc4-ab64-13984162d678">
<p>お気に入り登録は地点詳細またはピンポイント検索で行い、これらはルート検索時等にドロップダウンに表示されるようになる　</p><br>
<h4>マイページ機能では投稿とお気に入り地点の確認及び編集が行える</h4>
<img alt="マイページ" src="https://github.com/user-attachments/assets/c7e5c966-af35-4c85-87dc-1bfac79c9d3c">
<h4>地点お気に入り機能はメモの追記、共有も可能</h4>
<img alt = "地点コメント編集" src = ""　>
<img alt="地点お気に入り機能" src="">

<h3>ルート登録・地点お気に入り登録</h3>
<h4>ルート検索結果を保存できる</h4>
<img alt = "ルート保存" src = ""　>
<p>複数地点検索、公共交通機関検索のボタンから保存</p>
<h4>マイページからコメントを追記、編集して共有することも可能</h4>
<img alt = "ルートコメント編集" src = ""　>
<img alt = "ルート共有" src = ""　>
<h3>今後の実装予定</h3>
<ul>
    <li>お気に入り地点の絞り込み機能</li>
    <li>googleカレンダーとの連携</li>
    <li>投稿いいね機能</li>
</ul><hr>
<h3>注力した機能</h3>
<h4>・検索フォーム</h4>
<p>検索フォームにはオートコンプリート機能をつけ、オートコンプリート機能が使用された際には取得した必要な情報をinputのタイプをhiddenのvalueに入れ込むことで、レイアウトには影響が出ないようにしながらも無駄な処理を省いて検索を行えるようにした。また、検索フォームを監視するJavascriptによって、hiddenに入力された値は検索フォームが空欄になった際にリセットされるようになっている。</p><br>

<h4>・お気に入り地点のドロップダウン</h4>
<p>ログイン時にのみLaravelのコントローラークラスを経由し、IDでリレーションされたお気に入り地点を取り出している。取り出したデータは普段はCSSでnoneのスタイルの適用によって表示されていないが、フォームをクリックされたときなどの処理をJavaScriptでおこなうことで、擬似的にHTMLのselectを再現している。</p><br>

<h4>・複数地点検索・公共交通機関検索での中間地点の追加</h4>
<p>中間地点の数をclickCountという変数で管理し、繰り返し処理、追加された全ての検索フォームにオートコンプリート機能とお気に入り地点のドロップダウンがつくようにしている。特にオートコンプリート機能は繰り返し処理の書き方が複雑で難しかった。</p><br>

<h4>・複数地点検索・公共交通機関検索での中間地点の検索</h4>
<p>それぞれにhiddenに値が入っていないかを確認し、hiddenに値が入っていない（必要な情報が取得できていない）場合には、それぞれにGoogleMap-APIのGeocording-APIによる地点検索を行う。この処理は非同期処理であったため、JavaScriptのPromiseを使用することによりすべての処理を確実に終わらせてからルート検索ができるようにしている。</p>
<hr>

<h3>使用技術</h3>
<h4>開発環境</h4>
<p>AWS, Laravel</p>
<h4>使用言語</h4>
<p>PHP, JavaScript</p>
<h4>機能</h4>
<p>Breeze, MariaDB, GoogleMap-API, RESAS-API , NAVITIME_API,GEMINI-API</p>
<h4>アプリトップページURL</h4>
<p>https://routecraft-deafc24a6bbe.herokuapp.com/</p>
