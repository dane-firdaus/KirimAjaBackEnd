<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxSalesLeadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_sales_lead', function (Blueprint $table) {
            $table->id();
            $table->string('corporate_name', 100);
            $table->enum('corporate_size', ['small', 'medium', 'enterprise']);
            $table->string('corporate_address', 255);
            $table->string('corporate_phone', 20);
            $table->string('corporate_email', 100);
            $table->date('start_lead_date');
            $table->date('end_lead_date');
            $table->boolean('status')->nullable()->default(false);
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
        Schema::dropIfExists('trx_sales_lead');
    }
}
