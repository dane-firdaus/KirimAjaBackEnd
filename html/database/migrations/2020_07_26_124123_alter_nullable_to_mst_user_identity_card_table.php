<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNullableToMstUserIdentityCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_identity_card', function (Blueprint $table) {
            $table->text('identity_card')->nullable()->change();
            $table->text('identity_card_selfie')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user_identity_card', function (Blueprint $table) {
            $table->text('identity_card')->nullable()->change();
            $table->text('identity_card_selfie')->nullable()->change();
        });
    }
}
