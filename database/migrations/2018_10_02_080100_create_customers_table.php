<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_name',200);
            $table->string('customer_nif',9);
            $table->string('customer_address',255);
            $table->string('customer_city',100);
            $table->string('customer_zip',5);
            $table->unsignedInteger('idcompany');
            $table->unsignedInteger('idmethod');            
            $table->timestamps();
            $table->foreign('idcompany')->references('id')->on('companies'); 
            $table->foreign('idmethod')->references('id')->on('payment_methods');             
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
