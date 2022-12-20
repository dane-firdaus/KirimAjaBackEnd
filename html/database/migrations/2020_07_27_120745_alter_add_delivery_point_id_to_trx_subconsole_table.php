<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddDeliveryPointIdToTrxSubconsoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_subconsole', function (Blueprint $table) {
            $table->integer('delivery_point_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_subconsole', function (Blueprint $table) {
            $table->integer('delivery_point_id')->unsigned()->nullable();
        });
    }
}
