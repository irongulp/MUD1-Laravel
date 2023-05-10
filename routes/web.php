<?php

use App\Http\Controllers\DebugRoomController;
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

Route::get('/debug/room', [DebugRoomController::class, 'index']);
Route::get('/debug/room/{room}', [DebugRoomController::class, 'get'])->name('debug-room');
