<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'=>'Administrador',
            'email'=>'admin@admin.com',
            'password'=> bcrypt('admin'),
            'google_id'=>'',
            'created_at'=>date(now()),
            'updated_at'=>date(now())
        ]);
    }
}
