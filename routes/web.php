<?php

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

Route::get('/', [App\Http\Controllers\HomeController::class, 'home'])->name('home');
Route::post('/generatePDF', [App\Http\Controllers\HomeController::class, 'generatePDF'])->name('generatePDF');
Route::post('/getDateDebutFin', [App\Http\Controllers\HomeController::class, 'getDateDebutFin'])->name('getDateDebutFin');
Route::post('/removeFile', [App\Http\Controllers\HomeController::class, 'removeFile'])->name('removeFile');
