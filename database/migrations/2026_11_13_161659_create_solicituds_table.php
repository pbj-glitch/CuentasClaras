<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicituds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('miembro_reclamado_id')->nullable()->constrained('miembros')->nullOnDelete();
            $table->string('estado')->default('pendiente'); // pendiente, aprobada, rechazada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};