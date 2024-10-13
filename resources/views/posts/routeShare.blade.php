<!DOCTYPE html>
<html lang="ja">
 
 <head>
    <meta charset="utf-8">
   <title>登録地点</title>
    <!--css-->
    <link href="{{ asset('/css/routeDetail.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"></script>
    <!--trixのファイル添付ボタンを非表示にする -->
    <style>
    trix-toolbar [data-trix-button-group="file-tools"] {
        display: none; 
    }
    </style>
</head>

<body>
    <p class = "title">{{$route->title}}</p>
    <p class = "content">{!! $route->content !!}</p>
    
   <div id = "memo" class = "memo">
        <div class = "memoForm">
            <p class = "content">{!! $route->memo !!}</p>
        </div>
    </div>
    <div id = "print"  class = "share">
       <button class = "print"  onclick = "window.print()">印刷</button>
    </div>
</body>
</html>