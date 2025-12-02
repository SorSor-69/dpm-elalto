<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCiExpedidoCelularToFuncionariosTable extends Migration
{
    public function up()
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->string('ci')->after('nombre');
            $table->string('expedido', 5)->after('ci');
            $table->string('celular')->nullable()->after('correo');
        });
    }

    public function down()
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn(['ci', 'expedido', 'celular']);
        });
    }
}
