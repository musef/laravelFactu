<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('inv_date')->default(now());
            $table->string('inv_number',15);
            
            $table->decimal('inv_base1',8,2)->default(0);
            $table->decimal('inv_cuota1',8,2)->default(0);
            $table->decimal('inv_base2',8,2)->default(0);
            $table->decimal('inv_cuota2',8,2)->default(0);
            $table->decimal('inv_base3',8,2)->default(0);
            $table->decimal('inv_cuota3',8,2)->default(0);
            $table->decimal('inv_total',9,2)->default(0);
                        
            $table->timestamp('inv_expiration')->default(now());
            
            $table->unsignedInteger('idcompany');
            $table->unsignedInteger('idcustomer');
            $table->unsignedInteger('idmethod');            
            $table->timestamps();
            
            $table->foreign('idcompany')->references('id')->on('companies'); 
            $table->foreign('idcustomer')->references('id')->on('customers');
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
        Schema::dropIfExists('invoices');
    }
}
