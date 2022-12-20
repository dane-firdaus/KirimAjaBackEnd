<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressToTrxMkt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_mkt', function (Blueprint $table) {
            $table->string('shipment_name', 80)->nullable();
            $table->string('shipment_phone', 12)->nullable();
            $table->string('shipment_address', 255)->nullable();
            $table->string('shipment_city', 255)->nullable();
            $table->string('shipment_zip_code', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_mkt', function (Blueprint $table) {
            //
        });
    }
}
