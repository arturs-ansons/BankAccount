<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::create('api_currencies', function (Blueprint $table) {
            $table->string('symbol', 3)->primary();
            $table->string('rate');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_currencies');
    }
}
