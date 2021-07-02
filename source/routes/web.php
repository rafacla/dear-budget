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

Route::middleware(['auth:sanctum', 'verified'])->get('/', App\Http\Livewire\Accounts::class)->name('account');

Route::middleware(['auth:sanctum', 'verified'])->get('/bank', App\Http\Livewire\Banks::class)->name('bank');
Route::middleware(['auth:sanctum', 'verified'])->get('/category', App\Http\Livewire\Categories::class)->name('category');
Route::middleware(['auth:sanctum', 'verified'])->get('/account', App\Http\Livewire\Accounts::class)->name('account');
Route::middleware(['auth:sanctum', 'verified'])->get('/budget', App\Http\Livewire\Budgets::class)->name('budget');
Route::middleware(['auth:sanctum', 'verified'])->get('/budget/{year}/{month}', App\Http\Livewire\Budgets::class)->name('budget.date');
Route::middleware(['auth:sanctum', 'verified'])->get('/transaction', App\Http\Livewire\Transactions::class)->name('transaction');
Route::middleware(['auth:sanctum', 'verified'])->get('/transaction/{year}/{month}', App\Http\Livewire\Transactions::class)->name('transaction.date');
Route::middleware(['auth:sanctum', 'verified'])->get('/account/autocomplete', [App\Http\Controllers\AutoCompleteAccountSearch::class,'search']);
