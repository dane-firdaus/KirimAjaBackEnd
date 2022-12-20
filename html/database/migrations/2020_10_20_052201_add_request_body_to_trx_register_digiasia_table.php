<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestBodyToTrxRegisterDigiasiaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_register_digiasia', function (Blueprint $table) {
            $table->longText('request_body')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_register_digiasia', function (Blueprint $table) {
            $table->longText('request_body')->nullable();
        });
    }
}
