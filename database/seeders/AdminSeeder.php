<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use app\Models\Inventory;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create an admin user
        User::create([
            'id' => 'admin', // Set the UID for admi
            'username' => 'admin', // Set the username for admin
            'email' => 'admin@example.com', // Set the admin email
            'password' => Hash::make('adminpassword'), // Set a password for the admin
            'role' => 'admin' // Set the role as admin
        ]);
    }
}
