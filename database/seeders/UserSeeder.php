<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Admin123!'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Crear un usuario regular de prueba
        User::create([
            'name' => 'Usuario Test',
            'email' => 'user@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('User123!'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Crear usuarios de prueba usando el factory
        User::factory(10)->create();
    }
}
