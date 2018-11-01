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
            'type'=>3,            
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
        
        DB::table('iva_rates')->insert([
            'idcompany'=>1,
            'iva_name'=>'Tipo Reducido',
            'rate'=> '10.00',          
            'type'=>2,            
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);

        DB::table('iva_rates')->insert([
            'idcompany'=>1,
            'iva_name'=>'Tipo Superreducido',
            'rate'=> '04.00',  
            'type'=>1,            
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
        
        DB::table('iva_rates')->insert([
            'idcompany'=>1,
            'iva_name'=>'Exento',
            'rate'=> '00.00',  
            'type'=>0,            
            'active'=>true,
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);        
        
    }
}
