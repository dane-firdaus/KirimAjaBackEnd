<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidStatusToTrxMkt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_mkt', function (Blueprint $table) {
            $table->boolean('is_paid')->nullable()->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_proff', 255)->nullable();
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
