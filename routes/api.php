<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//alat
Route::post("ambil/data", "apiC@ambil");
Route::post("kirim/data", "apiC@kirim");
Route::post("normalkan/data", "apiC@normalkan");

//android
//android
Route::post('login', "apiC@login");
