<!DOCTYPE html>
<html lang="ja">
 
 <head>
    <meta charset="utf-8">
   <title>登録地点</title>
    <!--css-->
    <link href="{{ asset('/css/routeDetail.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"></script>
    <style>
        trix-toolbar [data-trix-button-group="file-tools"] {
        display: none; 
    }
    </style>
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
        
    <p class = "title">{{$route->title}}</p>
    <p class = "content">{!! $route->content !!}</p>
    @if(!empty($route->memo))
        <div id = "memo" class = "memo">
            <div class = "memoForm">
                <p class = "content">{!! $route->memo !!}</p>
            </div>
        </div>
    @endif
    <button onclick = "editComment()" class = "button">コメント追加・編集</button>
    <div id = "form"></div>
    <div id = "share" class = "share">
        <a class = "line" href="https://social-plugins.line.me/lineit/share?url={{url('/posts/routeShare/'.$route->id)}}" target="_blank">Lineで共有</a>
       <a class = "mail" href="mailto:?subject={{$route->title}}&body=旅行予定{{url('/posts/routeShare/'.$route->id)}}">Mailで共有</a>
    </div>
    <div id = "print"  class = "share">
       <button class = "print"  onclick = "window.print()">印刷</button>
    </div>
    <div class="returnContent">
        <a class = "return" href = "/maps/routeEdit">戻る</a>
    </div>
    
    <script>
        function editComment(){
            document.getElementById("form").innerHTML = `<div  class = "form">
                                                            <form action="/posts/routeMemo/{{ $route->id }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <input id="editMemo" type="hidden" name="memo" value = "{{$route->memo}}">
                                                                <trix-editor input="editMemo"></trix-editor>
                                                                <input type = "submit" class = "submit"  value = "変更">
                                                            </form>
                                                        </div>`;
            //ボタンを非表示にし、フォームを表示
            document.getElementById("memo").style.display = 'none';
            document.getElementById("share").style.display = 'none';
            document.getElementById("print").style.display = 'none';
        }
        
    </script>
</body>
</html>