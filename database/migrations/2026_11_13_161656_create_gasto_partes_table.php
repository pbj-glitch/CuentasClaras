<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gasto_partes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_item_id')->constrained('gasto_items')->cascadeOnDelete();
            $table->foreignId('miembro_id')->constrained('miembros')->cascadeOnDelete();
            $table->integer('monto_asignado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_partes');
    }
};
