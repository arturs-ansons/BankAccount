<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('balances', function (Blueprint $table) {
            // balances migration
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('iban')->unique();
            $table->string('account_type');
            $table->string('currency');
            $table->decimal('balance', 18, 15)->default(0.00);
            $table->decimal('avgBtcPrice', 18, 15)->default(0.00);
            $table->timestamps();

        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('balances');
    }
}
