<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CotizacionController; 

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/cotizacion/convertir', [CotizacionController::class, 'convertir']);
Route::get('/cotizacion/guardar', [CotizacionController::class, 'guardarCotizaciones']);
Route::get('/cotizacion/promedio', [CotizacionController::class, 'promedioMensual']);


