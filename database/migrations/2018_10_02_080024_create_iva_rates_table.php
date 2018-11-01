<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIvaRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iva_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('idcompany');
            $table->string('iva_name',200);
            $table->decimal('rate',4,2)->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('type')->default(3);
            $table->timestamps();
            $table->foreign('idcompany')->references('id')->on('companies');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iva_rates');
    }
}
