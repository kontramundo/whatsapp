<?php

use App\Http\Controllers\ContactController;
use App\Livewire\ChatComponent;
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

Route::get('/', function () {
    return view('welcome');
});

Route::resource('contacts', ContactController::class)->except(['show'])->middleware('auth');

Route::get('/chat', ChatComponent::class)->middleware('auth')->name('chat.index');
