<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CotizacionController;

Route::get('/', function () {
    return view('welcome');
});

// Vista web de cotizaciones
Route::get('/cotizacion', [CotizacionController::class, 'vistaCotizacion'])->name('cotizacion.vista');

// Endpoint JSON para recargar tabla con JS
Route::get('/cotizacion/json', [CotizacionController::class, 'jsonCotizaciones']);


