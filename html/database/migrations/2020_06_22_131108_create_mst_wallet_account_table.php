<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstWalletAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_wallet_account', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('phone_number', 20);
            $table->string('account_number', 30)->nullable();
            $table->string('partner_token', 100)->nullable();
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
        Schema::dropIfExists('mst_wallet_account');
    }
}
