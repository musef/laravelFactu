<?php

use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_methods')->insert([
            'idcompany'=>1,
            'payment_method'=>'CONTADO',
            'diff'=> 0,
            'payment_day'=>0,           
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
        
        DB::table('payment_methods')->insert([
            'idcompany'=>1,
            'payment_method'=>'TRANSFERENCIA A 90 DÍAS',
            'diff'=> 90,
            'payment_day'=>0,           
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);        
    }
}
