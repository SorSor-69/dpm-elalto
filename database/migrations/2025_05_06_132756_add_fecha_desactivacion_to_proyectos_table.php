<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'fecha_desactivacion' a la tabla 'proyectos'.
     */
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->timestamp('fecha_desactivacion')->nullable()->after('activo');
        });
    }

    /**
     * Elimina la columna 'fecha_desactivacion' si se revierte la migración.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('fecha_desactivacion');
        });
    }
};
