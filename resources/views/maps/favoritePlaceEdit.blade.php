<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
</head>

<body>
    <!--お気に入り地点の削除-->
    <div class = "favoritePlace">
        <form action='/maps/deleteFavoritePlace',  method="POST">
            @csrf
            @method('DELETE')
            @foreach($favoritePlaces as $favoritePlace)
            
                <label>
                    {{-- valueを'$subjectのid'に、nameを'配列名[]'に --}}
                    <input type="checkbox" value="{{ $favoritePlace->id }}" name = 'favoritePlace_array[]' >
                        {{$favoritePlace->name}}
                    </input>
                </label>
                
            @endforeach 
            <input type = "submit">
        </form>
    </div>
    <a href = "/posts/mypage/{{Auth::id()}}">戻る</a>