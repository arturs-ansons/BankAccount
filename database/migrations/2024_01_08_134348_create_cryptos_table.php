<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCryptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cryptos', function (Blueprint $table) {
            $table->id();
            $table->string('crypto_name');
            $table->decimal('usd_rate', 18, 15)->default(0.0);
            $table->timestamps();
        });

        // Insert rows for each cryptocurrency
        DB::table('cryptos')->insert([
            ['crypto_name' => 'BTC'],
            ['crypto_name' => 'ETH'],
            ['crypto_name' => 'XRP'],
            // Add more cryptocurrencies if needed
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto', function (Blueprint $table) {
            //
        });
    }
}
