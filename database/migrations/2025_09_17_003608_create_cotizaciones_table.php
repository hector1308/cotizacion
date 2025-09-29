<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_dolar'); // 'oficial', 'blue'
            $table->enum('tipo_valor', ['compra', 'venta']);
            $table->decimal('valor', 10, 2);
            $table->timestamp('fecha')->index();
            $table->timestamps(); // opcional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};

