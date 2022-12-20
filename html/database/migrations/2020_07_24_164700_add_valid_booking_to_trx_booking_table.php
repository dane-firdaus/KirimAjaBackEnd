<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidBookingToTrxBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_booking', function (Blueprint $table) {
            $table->boolean('valid_booking')->nullable()->default(false);
            $table->boolean('reject_booking')->nullable()->default(false);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_booking', function (Blueprint $table) {
            $table->boolean('valid_booking')->nullable()->default(false);
            $table->boolean('reject_booking')->nullable()->default(false);
            $table->softDeletes();
        });
    }
}
