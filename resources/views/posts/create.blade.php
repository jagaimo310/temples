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
    {{Auth::user()->id}}
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
            <h2>場所</h2>
            <select name="post[place_id]">
                  @foreach($places as $place)
                    <option value={{ $place->id }}>
                      {{ $place->prefecture }}：{{ $place->area }}
                    </option>
                  @endforeach
            </select>
        </div>
        
        <!-- カテゴリーフォーム -->
        @foreach($categories as $category)

            <label>
                {{-- valueを'$subjectのid'に、nameを'配列名[]'に --}}
                <input type="checkbox" value="{{ $category->id }}" name="categories_array[]">
                    {{$category->name}}
                </input>
            </label>
            
        @endforeach  
        <!-- コメントフォーム -->
        <div class="comment">
            <h2>コメント</h2>
            <textarea name="post[comment]" placeholder="コメントを入力してください">{{old('post.comment')}}</textarea>
            <p class="comment_error" style="color:red;">{{$errors->first('post.comment')}}</p>
        </div>
        
        <!-- 写真フォーム -->
        <div class="photo">
            <h2>写真</h2>
            <input type="file" name="image">
            <p class="photo_error" style="color:red;">{{$errors->first('post.photo')}}</p>
        </div>
        
        <!-- 送信用ボタン -->
        <input type="submit" name="送信">
    </form>
</body>