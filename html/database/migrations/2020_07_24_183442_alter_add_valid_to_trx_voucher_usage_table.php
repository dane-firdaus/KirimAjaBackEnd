<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddValidToTrxVoucherUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_voucher_usage', function (Blueprint $table) {
            $table->boolean('is_valid')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_voucher_usage', function (Blueprint $table) {
            $table->boolean('is_valid')->nullable()->default(false);
        });
    }
}
