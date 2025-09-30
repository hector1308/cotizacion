<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CotizacionController;

// Ruta principal apuntando al dashboard de cotizaciones
Route::get('/', [CotizacionController::class, 'vistaCotizacion'])->name('cotizacion.vista');

// Endpoint JSON para recargar tabla con JS
Route::get('/cotizacion/json', [CotizacionController::class, 'jsonCotizaciones']);


