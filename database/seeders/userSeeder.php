<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'cuatro',
            'email' => 'cuatro@gmail.com',
            'password' => 'cuatropwd'
        ]);
        $user->assignRole('admin');
    }
}
