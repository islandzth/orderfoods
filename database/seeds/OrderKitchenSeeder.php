<?php

use Illuminate\Database\Seeder;

class OrderKitchenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kitchens')->insert([
            'id' => 1,
            'name' => "Bếp 1",
        ]);

        DB::table('kitchens')->insert([
            'id' => 2,
            'name' => "Bếp 2",
        ]);

        DB::table('users')->insert([
            'name' => "Nhân viên Order",
            'email' => 'order1@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => \App\User::ORDER_ROLE,
        ]);

        DB::table('users')->insert([
            'name' => "Nhân viên Bếp 1",
            'email' => 'kitchen1@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => \App\User::KITCHEN_ROLE,
            'kitchen_id' => 1,
        ]);

        DB::table('users')->insert([
            'name' => "Nhân viên Bếp 2",
            'email' => 'kitchen2@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => \App\User::KITCHEN_ROLE,
            'kitchen_id' => 2,
        ]);

        DB::table('users')->insert([
            'name' => "Nhân viên phục vụ",
            'email' => 'waiter@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => \App\User::WAITER_ROLE,
        ]);
    }
}
