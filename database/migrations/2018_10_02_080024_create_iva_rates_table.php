<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * El IVA actualmente está configurado 4 clases:
 * Exento, superreducido, reducido y general.
 * 
 * Esto se controla con type.
 * El type = -1 es un default sin funcionamiento para facturación, cuyo
 * único objeto es el de crear una id=1 que sea el default a donde apuntar
 * los tipos de iva vacíos en factura, asi evitamos el constraint integrity
 * violation por tener un indice que apunta a nada.
 * 
 * El type=-1 no deberá ser mostrado en los listados de tipos de IVA 
 * 
 */
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
            $table->boolean('type')->default(-1);
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
