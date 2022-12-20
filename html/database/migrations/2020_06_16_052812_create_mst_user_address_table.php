<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_user_address', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('address_alias', 255);
            $table->string('address_recepient', 255);
            $table->string('address_address', 255);
            $table->string('address_subdistrict', 255);
            $table->string('address_district', 255);
            $table->string('address_province', 255);
            $table->string('address_zip', 255);
            $table->string('address_phone', 25);
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
        Schema::dropIfExists('mst_user_address');
    }
}
