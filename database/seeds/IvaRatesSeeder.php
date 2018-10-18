<?php

use Illuminate\Database\Seeder;

class IvaRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('iva_rates')->insert([
            'idcompany'=>1,
            'iva_name'=>'Tipo General',
            'rate'=> '21.00',
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
        
        DB::table('iva_rates')->insert([
            'idcompany'=>1,
            'iva_name'=>'Tipo Reducido',
            'rate'=> '10.00',          
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);

        DB::table('iva_rates')->insert([
            'idcompany'=>1,
            'iva_name'=>'Tipo Superreducido',
            'rate'=> '04.00',  
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
        
    }
}
