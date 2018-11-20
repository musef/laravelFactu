<?php

use Illuminate\Database\Seeder;

class ConfigsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'createUsers',
            'value'=> 'No'
        ]);
        
        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'usingRoles',
            'value'=> 'No'
        ]);
        
        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'createCompanies',
            'value'=> 'No'
        ]);

        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'worksmode',
            'value'=> '1'
        ]);

        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'workPrefix',
            'value'=> 'ALB'
        ]);

        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'worknumPrefix',
            'value'=> '1'         
        ]);
        
        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'worknumLength',
            'value'=> '15'
        ]);        

        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'invoiceSerial',
            'value'=> ''
        ]);        
        
        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'invoicePrefix',
            'value'=> '2'
        ]);

        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'invoicenumLength',
            'value'=> '15'
        ]); 

        DB::table('configs')->insert([
            'idcompany'=>1,
            'name'=>'invoiceNote',
            'value'=> 'Empresa Prueba con NIF ZZZZZZZZZ e inscrita en el registro z con el número xxxxxx tomo xxxxxx seccion xxxxxxxxxx pagina xxxxx'
        ]);
        
    }
}
