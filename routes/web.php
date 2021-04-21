<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', 'verified'])->get('/', App\Http\Livewire\Categories::class)->name('categories');

Route::middleware(['auth:sanctum', 'verified'])->get('/bank', App\Http\Livewire\Banks::class)->name('bank');
Route::middleware(['auth:sanctum', 'verified'])->get('/category', App\Http\Livewire\Categories::class)->name('category');
