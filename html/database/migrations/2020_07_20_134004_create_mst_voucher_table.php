<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_voucher', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_id')->unsigned();
            $table->string('voucher_name', 100);
            $table->string('voucher_code', 10);
            $table->integer('voucher_value')->unsigned();
            $table->date('voucher_valid')->nullable();
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
        Schema::dropIfExists('mst_voucher');
    }
}
