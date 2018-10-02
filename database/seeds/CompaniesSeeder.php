<?php

use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            'company_name'=>'Empresa Prueba S.L.',
            'company_nif'=>'A28000000',
            'company_address'=> 'Alcala, 2',
            'company_city'=>'Madrid',
            'company_zip'=>'28000',            
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
    }
}
