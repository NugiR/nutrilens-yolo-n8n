<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::factory()->create([
            'name'      => 'Angela Nazareta',
            'full_name' => 'Angela Nazareta',
            'email'     => 'angela@nutrilens.test',
            'password'  => bcrypt('password123'),
            'gender'    => 'perempuan',
            'height_cm' => 165,
            'weight_kg' => 58,
        ]);
    }
}
