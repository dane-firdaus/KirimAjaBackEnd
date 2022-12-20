<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstPartnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_partner', function (Blueprint $table) {
            $table->id();
            $table->string('partner_name', 255);
            $table->string('partner_address', 255);
            $table->string('partner_email', 100);
            $table->string('partner_phone', 25);
            $table->string('partner_pic', 50);
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
        Schema::dropIfExists('mst_partner');
    }
}
