<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstFoodMkt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_food_mkt', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('store_id')->nullable();
            $table->string('store_name', 255)->nullable();
            $table->string('location_city_name', 255)->nullable();
            $table->string('location_city_code', 255)->nullable();
            $table->double('price')->nullable();
            $table->integer('min_weight')->nullable()->default(1);
            $table->boolean('is_recommend')->nullable()->default(false);
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
        Schema::dropIfExists('mst_food_mkt');
    }
}
