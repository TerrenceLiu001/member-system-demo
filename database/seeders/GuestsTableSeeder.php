<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // 通常會用到 DB facade

class GuestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('member_center_guests')->insert([
            'email' => 'test@example.com',
            'register_token' => 'test',
            'token_expires_at' => '2027-07-08 03:12:11',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
