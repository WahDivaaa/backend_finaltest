<?php

use App\Http\Controllers\AuthorsController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\RatingsController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [BooksController::class, 'index'])->name('books.index');
Route::get('books', [BooksController::class, 'index'])->name('books.index');
Route::get('author', [AuthorsController::class, 'index'])->name('author.index');
Route::get('/author/{id}/books', [AuthorsController::class, 'getBooks']);
Route::resource('rating', RatingsController::class);