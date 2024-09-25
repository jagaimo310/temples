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
                        <a href = "/maps/{{$favoritePlace->name}}?lat={{$favoritePlace->latitude}}&lng={{$favoritePlace->longitude}}&id={{$favoritePlace->place_id}}&name={{$favoritePlace->name}}">{{$favoritePlace->name}} {{$favoritePlace->prefecture}}{{$favoritePlace->area}}</a></br>
                    </input>
                </label>
                
            @endforeach 
            <input type = "submit" value = "削除">
        </form>
    </div>
    <a href = "/posts/mypage">戻る</a>