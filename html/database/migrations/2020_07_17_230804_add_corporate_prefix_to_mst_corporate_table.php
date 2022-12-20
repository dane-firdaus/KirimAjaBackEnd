<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorporatePrefixToMstCorporateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_corporate', function (Blueprint $table) {
            $table->string('prefix', 5)->default('PREF');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_corporate', function (Blueprint $table) {
            $table->string('prefix', 5)->default('PREF');
        });
    }
}
