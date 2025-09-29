<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Cotizacion;
use Carbon\Carbon;

class CotizacionController extends Controller
{
    /**
     * Convertir USD a pesos usando cotización actual
     */
    public function convertir(Request $request)
    {
        $valorUSD = $request->query('valor');
        $tipo = $request->query('tipo', 'oficial');

        if (!$valorUSD || !is_numeric($valorUSD)) {
            return response()->json(['error' => 'Debe enviar un valor numérico en dólares.'], 400);
        }

        $baseUrl = config('services.dolarapi.url');
        $response = Http::get("{$baseUrl}/{$tipo}");

        if ($response->failed()) {
            return response()->json(['error' => 'No se pudo obtener la cotización.'], 500);
        }

        $data = $response->json();
        $cotizacion = $data['venta'] ?? null;

        if (!$cotizacion) {
            return response()->json(['error' => 'Cotización no disponible.'], 500);
        }

        $resultado = $valorUSD * $cotizacion;

        return response()->json([
            'tipo' => $tipo,
            'valor_dolar' => $valorUSD,
            'cotizacion' => $cotizacion,
            'resultado_en_pesos' => round($resultado, 2)
        ]);
    }

    /**
     * Guardar cotizaciones actuales (compra y venta, oficial y blue)
     */
    public function guardarCotizaciones()
    {
        $baseUrl = config('services.dolarapi.url');
        $response = Http::get($baseUrl);

        if ($response->failed()) {
            \Log::error('❌ Error al obtener cotización de API');
            return response()->json(['error' => 'No se pudo obtener la cotización.'], 500);
        }

        $data = $response->json();
        $fecha = now();

        foreach (['oficial', 'blue'] as $tipo) {
            $registro = collect($data)->firstWhere('casa', $tipo);

            if (!$registro) {
                \Log::warning("⚠️ No se encontró cotización para tipo '$tipo'");
                continue;
            }

            $compra = $registro['compra'] ?? null;
            $venta = $registro['venta'] ?? null;

            if ($compra) {
                Cotizacion::create([
                    'tipo_dolar' => $tipo,
                    'tipo_valor' => 'compra',
                    'valor' => $compra,
                    'fecha' => $fecha,
                ]);
            }

            if ($venta) {
                Cotizacion::create([
                    'tipo_dolar' => $tipo,
                    'tipo_valor' => 'venta',
                    'valor' => $venta,
                    'fecha' => $fecha,
                ]);
            }
        }

        return response()->json(['message' => '✅ Cotizaciones guardadas correctamente']);
    }

    /**
     * Obtener el promedio mensual de cotizaciones
     */
    public function promedioMensual(Request $request)
    {
        $validated = $request->validate([
            'anio' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12',
            'tipo_dolar' => 'required|string|in:oficial,blue',
            'tipo_valor' => 'required|string|in:compra,venta',
        ]);

        $promedio = Cotizacion::whereYear('fecha', $validated['anio'])
            ->whereMonth('fecha', $validated['mes'])
            ->where('tipo_dolar', $validated['tipo_dolar'])
            ->where('tipo_valor', $validated['tipo_valor'])
            ->avg('valor');

        if (is_null($promedio)) {
            return response()->json(['message' => 'No hay datos para ese mes y tipo.'], 404);
        }

        return response()->json([
            'anio' => $validated['anio'],
            'mes' => $validated['mes'],
            'tipo_dolar' => $validated['tipo_dolar'],
            'tipo_valor' => $validated['tipo_valor'],
            'promedio' => round($promedio, 2)
        ]);
    }

    /**
     * Vista web con Blade para ver las cotizaciones guardadas
     */
    public function vistaCotizacion()
    {
        $cotizaciones = Cotizacion::latest('fecha')->take(50)->get();

        $promedio = Cotizacion::whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->where('tipo_dolar', 'oficial')
            ->where('tipo_valor', 'venta')
            ->avg('valor');

        return view('cotizacion.index', compact('cotizaciones', 'promedio'));
    }

    /**
     * Devuelve las últimas 50 cotizaciones en JSON
     */
    public function jsonCotizaciones()
    {
        return Cotizacion::latest('fecha')->take(50)->get();
    }
}




