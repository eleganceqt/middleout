<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/articles')->group(function () {

    Route::get('/', [ArticleController::class, 'index'])->name('articles.index');

    Route::post('/', [ArticleController::class, 'store'])->name('articles.store');

    Route::put('/{id}', [ArticleController::class, 'update'])->name('articles.update');

    Route::delete('/{id}', [ArticleController::class, 'destroy'])->name('articles.destroy');

});
