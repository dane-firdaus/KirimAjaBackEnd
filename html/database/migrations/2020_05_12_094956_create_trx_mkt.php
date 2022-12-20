<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxMkt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_mkt', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('booking', 10);
            $table->string('product_type', 10);
            $table->double('total_cost');
            $table->double('shipment_cost');
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
        Schema::dropIfExists('trx_mkt');
    }
}
