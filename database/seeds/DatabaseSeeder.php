<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersSeeder::class);
        $this->call(CompaniesSeeder::class);
        $this->call(PaymentMethodsSeeder::class);
        $this->call(IvaRatesSeeder::class);
    }
}
