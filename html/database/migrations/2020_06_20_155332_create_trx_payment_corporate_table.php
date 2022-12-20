<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxPaymentCorporateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_payment_corporate', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id')->unsigned();
            $table->enum('payment_type', ['deposit', 'terms']);
            $table->integer('deposit_id')->unsigned()->nullable();
            $table->integer('terms_length')->unsigned()->nullable();
            $table->float('amount');
            $table->float('tax');
            $table->float('comission_amount');
            $table->integer('comission_by');
            $table->boolean('paid')->nullable()->default(false);
            $table->dateTime('paid_at')->nullable();
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
        Schema::dropIfExists('trx_payment_corporate');
    }
}
