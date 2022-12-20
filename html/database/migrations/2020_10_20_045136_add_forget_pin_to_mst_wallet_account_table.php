<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForgetPinToMstWalletAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_wallet_account', function (Blueprint $table) {
            $table->string('forget_pin', 100)->nullable();
            $table->timestamp('forget_pin_validity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_wallet_account', function (Blueprint $table) {
            $table->string('forget_pin', 100)->nullable();
        });
    }
}
