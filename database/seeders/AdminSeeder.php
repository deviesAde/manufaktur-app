<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {

        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'name' => 'Administrator Pt Garment',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('agbagbagb'),
                'email_verified_at' => now(),

            ]);
        }
    }
}
