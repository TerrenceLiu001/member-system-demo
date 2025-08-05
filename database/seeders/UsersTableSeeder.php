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
        $faker = Faker::create('zh_TW');  

        $gender = ['male', 'female', 'unknown'];
        $age_group = ['under_20', 'between_21_30', 'between_31_40', 'between_41_50', 'between_51_60', 'below_61'];

        // foreach (range(1, 1) as $index) {
        //     DB::table('member_center_users')->insert([
        //         'guest_id' => $index,
        //         'username' => 'TestAccount',
        //         'email' => $faker->unique()->safeEmail,
        //         'mobile' => '0900123456',
        //         'country' => 'TWN', 
        //         'gender' => $faker->randomElement($gender),
        //         'age_group' => $faker->randomElement($age_group),
        //         'address' => $faker->address,
        //         'password' => Hash::make('Test0000'),
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        DB::table('member_center_users')->insert([
            'guest_id' => 100,
            'username' => 'TestAccount',
            'email' => 'test@gmail.com',
            'mobile' => '0900123456',
            'country' => 'TWN', 
            'password' => Hash::make('Test0000'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
