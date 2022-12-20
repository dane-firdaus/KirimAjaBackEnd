<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxPaymentRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_payment_request', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id')->unsigned();
            $table->string('transid', 100);
            $table->string('status_code', 5)->nullable();
            $table->string('va_number', 100)->nullable();
            $table->boolean('paid')->nullable()->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->string('paid_channel', 100)->nullable();
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
        Schema::dropIfExists('trx_payment_request');
    }
}
