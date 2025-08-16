<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $emailId = [

        ];
        
        $gender = [
            'male', 
            'female', 
            'unknown'
        ];


        $age_group = [
            'under_20', 
            'between_21_30', 
            'between_31_40', 
            'between_41_50', 
            'between_51_60', 
            'below_61'
        ];

        DB::table('member_center_users')->insert([
            'guest_id' => 100,
            'username' => 'TestAccount',
            'email' => 'test01@test.com',
            'country' => 'TWN', 
            'password' => Hash::make('Test0000'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($i = 1; $i <= 50; $i++){

            $guest_id = 100 + $i;
            $email = 'test' . str_pad($emailId[$i-1], 3, '0', STR_PAD_LEFT) . '@test.com';
            
            DB::table('member_center_users')->insert([
                'guest_id' => $guest_id,
                'username' => 'TestAccount',
                'email' => $email,
                'country' => 'TWN', 
                'password' => Hash::make('Test0000'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
