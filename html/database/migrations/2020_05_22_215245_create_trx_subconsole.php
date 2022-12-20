<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxSubconsole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_subconsole', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('booking_id');
            $table->float('transaction_comission_amount');
            $table->integer('transaction_comission_by');
            $table->boolean('valid');
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
        Schema::dropIfExists('trx_subconsole');
    }
}
