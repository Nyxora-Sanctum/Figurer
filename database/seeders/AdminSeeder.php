<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\accounts;
use App\Models\Inventory;
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
        accounts::create([
            'id' => '0', // Set the UID for admi
            'username' => 'admin', // Set the username for admin
            'email' => 'admin@example.com', // Set the admin email
            'password' => Hash::make('adminpassword'), // Set a password for the admin
            'role' => 'admin' // Set the role as admin
        ]);
        Inventory::create([
            'available_items' => json_encode(['available_items' => 0]),
            'used_items' => json_encode(['used_items' => 0]),
        ]);
    }
}
