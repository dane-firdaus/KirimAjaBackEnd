<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstCityBranchOfficeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_city_branch_office', function (Blueprint $table) {
            $table->id();
            $table->string('city_name', 100)->nullable();
            $table->string('airport_code', 3)->nullable();
            $table->string('branch_office_area', 3)->nullable();
            $table->string('region_area', 3)->nullable();
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
        Schema::dropIfExists('mst_city_branch_office');
    }
}
