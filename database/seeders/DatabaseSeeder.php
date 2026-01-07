<?php

namespace Database\Seeders;

use App\Features\User\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Eskie Maquilang',
            'email' => 'eskiesiriusmaquilang@gmail.com',
            'username' => 'eskie',
            'password' => '123123123',
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'aa@aa.com',
            'username' => 'test',
            'password' => '123123123',
        ]);
    }
}
