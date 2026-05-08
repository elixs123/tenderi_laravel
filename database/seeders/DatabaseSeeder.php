<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'elvis@pennyplus.com'],
            
            [
                'first_name' => 'Elvis',
                'last_name' => 'Sarajčić',
                'password' => Hash::make('admin'),
                'role' => 'admin'
            ]
        );

        
        User::factory(10)->create();
    }
}