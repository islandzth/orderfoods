<?php

use Illuminate\Database\Seeder;

class GeneralKitchenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "Bếp tổng",
            'email' => 'general_kitchen@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => \App\User::GENERAL_KITCHEN_ROLE,
        ]);
    }
}
