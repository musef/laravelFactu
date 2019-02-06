<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('works', function (Blueprint $table) {
            $table->increments('id');
            $table->string('work_code',10)->nullable();            
            $table->timestamp('work_date')->default(now());
            $table->string('work_number',15);
            $table->text('work_text');
            $table->decimal('work_qtt',7,2)->default(0);
            $table->decimal('work_price',7,2)->default(0);
            $table->decimal('work_total',9,2)->default(0);            
            $table->unsignedInteger('idinvoice')->default(0);            
            $table->unsignedInteger('idcompany');
            $table->unsignedInteger('idcustomer');
            $table->unsignedInteger('idiva');            
            $table->timestamps();
            $table->foreign('idcompany')->references('id')->on('companies'); 
            $table->foreign('idcustomer')->references('id')->on('customers');
            $table->foreign('idiva')->references('id')->on('iva_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('works');
    }
}
