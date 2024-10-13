<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/', [PostController::class,'top'])->name('top');
Route::get('/maps/navi', [PostController::class,'navi'])->name('navi');
Route::get('/maps/place', [PostController::class,'place'])->name('place');
Route::get('/maps/search', [PostController::class,'search'])->name('search');
Route::get('/maps/favoriteplaceEdit', [PostController::class,'favoriteplaceEdit'])->name('favoriteplaceEdit');
Route::get('/maps/severalRoute', [PostController::class,'severalRoute'])->name('severalRoute');
Route::get('/maps/routeEdit', [PostController::class,'routeEdit'])->name('routeEdit');
Route::get('/maps/{name}', [PostController::class,'detail'])->name('detail');
Route::get('/posts/mypage', [PostController::class,'myPage'])->name('myPage')->middleware(['auth']);
Route::get('/posts/postsAll',[PostController::class,'postsAll'])->name('postsAll');
Route::get('/posts/create',[PostController::class,'create'])->name('create')->middleware(['auth']);
Route::get('/posts/routeDetail/{route}',[PostController::class,'routeDetail'])->name('routeDetail');
Route::get('/posts/routeShare/{route}',[PostController::class,'routeShare'])->name('routeShare');
Route::get('/posts/placeComment/{favoritePlace}', [PostController::class,'placeComment'])->name('placeComment');
Route::get('/posts/placeShare/{favoritePlace}', [PostController::class,'placeShare'])->name('placeShare');
Route::get('/posts/{post}', [PostController::class,'show'])->name('show');
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('edit');
Route::post('/posts',[PostController::class,'store'])->name('store');
Route::post('/maps',[PostController::class,'favoritePlace'])->name('favoritePlace');
Route::post('/retrieval',[PostController::class,'retrieval'])->name('retrieval');
Route::post('/saveRoute',[PostController::class,'saveRoute'])->name('saveRoute');
Route::delete('/posts/{post}', [PostController::class,'delete'])->name('delete');
Route::delete('/maps/deleteFavoritePlace', [PostController::class,'deleteFavoritePlace']);
Route::delete('/maps/deleteRoute', [PostController::class,'deleteRoute']);
Route::put('/posts/{post}', [PostController::class, 'update'])->name('update');
Route::put('/posts/placeComment/{favoritePlace}', [PostController::class, 'favoritePlaceUpdate'])->name('favoritePlaceUpdate');
Route::put('/posts/routeMemo/{route}', [PostController::class, 'routeUpdate'])->name('routeUpdate');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
