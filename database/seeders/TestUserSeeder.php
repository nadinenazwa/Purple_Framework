<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate([
            'email' => 'tester@example.test'
        ], [
            'name' => 'Tester',
            'password' => bcrypt('secret123'),
        ]);
    }
}
