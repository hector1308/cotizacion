@extends('layouts.app')

@section('title','Cotización del dólar')

@section('content')
<h1>Cotización del dólar</h1>

<!-- Formulario de conversión -->
<div style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ccc; background: #fafafa;">
    <h2>Convertir USD a pesos argentinos</h2>
    <form id="convertForm">
        <label for="valor">Valor en USD:</label>
        <input type="number" id="valor" name="valor" required step="0.01">
        
        <label for="tipo">Tipo de dólar:</label>
        <select id="tipo" name="tipo">
            <option value="oficial">Oficial</option>
            <option value="blue">Blue</option>
        </select>

        <button type="submit">Convertir</button>
    </form>
    <p id="resultadoConversion" style="margin-top:1rem; font-weight:bold;"></p>
</div>

<!-- Botón para guardar cotizaciones actuales -->
<div style="margin-bottom: 2rem;">
    <button id="guardarCotizacionesBtn" style="padding:0.5rem 1rem;">Guardar cotizaciones actuales</button>
    <p id="guardarMensaje" style="margin-top:0.5rem; font-weight:bold;"></p>
</div>

<!-- Selección de mes y año para promedio -->
@php
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
@endphp

<div style="margin-bottom: 1rem;">
    <label for="mesPromedio">Mes:</label>
    <select id="mesPromedio">
        @foreach($meses as $num => $nombre)
            <option value="{{ $num }}" {{ $num == now()->month ? 'selected' : '' }}>
                {{ $nombre }}
            </option>
        @endforeach
    </select>

    <label for="anioPromedio">Año:</label>
    <select id="anioPromedio">
        @for($i = now()->year-5; $i <= now()->year; $i++)
            <option value="{{ $i }}" {{ $i == now()->year ? 'selected' : '' }}>
                {{ $i }}
            </option>
        @endfor
    </select>
</div>

<!-- Promedio del mes -->
<p>
    Promedio (oficial venta): <strong>$<span id="promedioMes">0.00</span></strong>
</p>

<!-- Tabla de cotizaciones -->
<table id="tablaCotizaciones">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo dólar</th>
            <th>Tipo valor</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($cotizaciones as $c)
            <tr>
                <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst($c->tipo_dolar) }}</td>
                <td>{{ ucfirst($c->tipo_valor) }}</td>
                <td>${{ number_format($c->valor,2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center;">No hay cotizaciones guardadas</td>
            </tr>
        @endforelse
    </tbody>
</table>

<script>
// Función para actualizar promedio según mes y año seleccionados
async function actualizarPromedio() {
    try {
        const mes = document.getElementById('mesPromedio').value;
        const anio = document.getElementById('anioPromedio').value;
        const tipo_dolar = 'oficial';
        const tipo_valor = 'venta';

        const response = await fetch(`/api/cotizacion/promedio?anio=${anio}&mes=${mes}&tipo_dolar=${tipo_dolar}&tipo_valor=${tipo_valor}`);
        const data = await response.json();

        const promedioEl = document.getElementById('promedioMes');
        if(data.promedio) {
            promedioEl.textContent = Number(data.promedio).toFixed(2);
        } else {
            promedioEl.textContent = '0.00';
        }
    } catch(err) {
        console.error('Error al actualizar promedio', err);
        document.getElementById('promedioMes').textContent = '0.00';
    }
}

// Actualizar promedio cuando se cambie mes o año
document.getElementById('mesPromedio').addEventListener('change', actualizarPromedio);
document.getElementById('anioPromedio').addEventListener('change', actualizarPromedio);

// Conversión USD → ARS
document.getElementById('convertForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const valor = document.getElementById('valor').value;
    const tipo = document.getElementById('tipo').value;
    const resultadoEl = document.getElementById('resultadoConversion');

    if (!valor) return;

    try {
        const response = await fetch(`/api/cotizacion/convertir?valor=${valor}&tipo=${tipo}`);
        const data = await response.json();
        if (data.error) {
            resultadoEl.textContent = "Error: " + data.error;
        } else {
            resultadoEl.textContent = `${data.valor_dolar} USD (${data.tipo}) = $${data.resultado_en_pesos} ARS`;
        }
    } catch (err) {
        resultadoEl.textContent = "Error al conectar con la API";
        console.error(err);
    }
});

// Guardar cotizaciones actuales
document.getElementById('guardarCotizacionesBtn').addEventListener('click', async function() {
    const mensajeEl = document.getElementById('guardarMensaje');
    mensajeEl.textContent = 'Guardando cotizaciones...';

    try {
        const response = await fetch('/api/cotizacion/guardar');
        const data = await response.json();

        if (data.message) {
            mensajeEl.textContent = data.message;

            // Recargar tabla
            const tablaBody = document.querySelector('#tablaCotizaciones tbody');
            const cotizacionesResponse = await fetch('/cotizacion/json');
            const cotizaciones = await cotizacionesResponse.json();

            tablaBody.innerHTML = '';
            if (cotizaciones.length > 0) {
                cotizaciones.forEach(c => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${new Date(c.fecha).toLocaleString()}</td>
                        <td>${c.tipo_dolar.charAt(0).toUpperCase() + c.tipo_dolar.slice(1)}</td>
                        <td>${c.tipo_valor.charAt(0).toUpperCase() + c.tipo_valor.slice(1)}</td>
                        <td>$${Number(c.valor).toFixed(2)}</td>
                    `;
                    tablaBody.appendChild(tr);
                });
            } else {
                tablaBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No hay cotizaciones guardadas</td></tr>';
            }

            // Actualizar promedio según mes/año seleccionados
            actualizarPromedio();

        } else {
            mensajeEl.textContent = 'Error al guardar';
        }
    } catch (err) {
        mensajeEl.textContent = 'Error al conectar con la API';
        console.error(err);
    }
});

// Inicializar promedio al cargar la página
actualizarPromedio();
</script>
@endsection




