<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidToTrxSubconsole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_subconsole', function (Blueprint $table) {
            $table->enum('valid', ['order', 'accepted', 'rejected'])->nullable()->default('order');
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
            //
        });
    }
}
