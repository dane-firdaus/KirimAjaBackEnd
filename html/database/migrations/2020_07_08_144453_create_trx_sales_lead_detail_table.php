<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxSalesLeadDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_sales_lead_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id')->unsigned();
            $table->date('follow_up_date')->nullable();
            $table->string('remark_follow_up_category', 50)->nullable();
            $table->longText('remark_follow_up')->nullable();
            $table->string('extra_note', 255)->nullable();
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
        Schema::dropIfExists('trx_sales_lead_detail');
    }
}
