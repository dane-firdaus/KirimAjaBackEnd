<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddAddressToMstDeliveryPointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_delivery_point', function (Blueprint $table) {
            $table->string('address', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_delivery_point', function (Blueprint $table) {
            $table->string('address', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
        });
    }
}
