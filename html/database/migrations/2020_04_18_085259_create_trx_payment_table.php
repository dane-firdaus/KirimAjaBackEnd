<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('booking_id');
            $table->float('transaction_amount');
            $table->float('transaction_tax');
            $table->float('transaction_comission_amount');
            $table->integer('transaction_comission_by');
            $table->string('transaction_id', 20)->nullable();
            $table->boolean('paid')->nullable()->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->string('paid_channel', 100)->nullable();
            $table->integer('paid_response')->nullable();
            $table->string('payment_proof', 255)->nullable();
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
        Schema::dropIfExists('trx_payment');
    }
}
