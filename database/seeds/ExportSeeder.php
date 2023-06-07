<?php

use Illuminate\Database\Seeder;

class ExportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "Acc Export Data",
            'email' => 'acc_export@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => \App\User::EXPORT_ROLE,
        ]);
    }
}
