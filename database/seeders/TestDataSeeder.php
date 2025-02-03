<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\Accounts;
use App\Models\Inventory;
use App\Models\Invoices;
use App\Models\Transactions;
use Faker\Factory as Faker;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Clear faker unique cache
        $faker->unique(true);

        for ($i = 0; $i < 15; $i++) {
            // Generate random timestamp between January and December 2025
            $randomTimestamp = $faker->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s');

            // Generate account
            $account = Accounts::create([
                'username' => $faker->userName,
                'email' => $faker->safeEmail, // Removed unique()
                'password' => bcrypt('password'),
                'gender' => $faker->randomElement(['male', 'female']),
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'role' => 'user',
                'created_at' => $randomTimestamp,
                'updated_at' => $randomTimestamp,
            ]);

            // Generate inventory
            Inventory::create([
                'available_items' => json_encode(['available_items' => $faker->randomNumber(2)]),
                'used_items' => json_encode(['used_items' => $faker->randomNumber(2)]),
                'created_at' => $randomTimestamp,
                'updated_at' => $randomTimestamp,
            ]);

            // Generate CV template
            $cvTemplate = Template::create([
                'name' => $faker->word, // Removed unique()
                'unique_cv_id' => $faker->uuid, // Removed unique()
                'price' => $faker->randomFloat(2, 5, 100),
                'template-link' => 'storage/template_links/VqZO8lNUBaDZPB6mVzKvPQSxu5AFR2PRCyeVQSe3.html',
                'template-preview' => 'storage/template_previews/0VrSifle2Kjhrt7miiU72s6lqgYNYxcCpfTyxC9K.png',
                'created_at' => $randomTimestamp,
                'updated_at' => $randomTimestamp,
            ]);

            // Generate invoice
            $orderId = $faker->uuid; // Removed unique()

            Invoices::create([
                'username' => $account->username,
                'invoice_id' => $faker->uuid, // Removed unique()
                'order_id' => $orderId,
                'status' => $faker->randomElement(['paid', 'unpaid']),
                'amount' => $faker->randomFloat(2, 10, 1000),
                'item_id' => $cvTemplate->unique_cv_id,
                'created_at' => $randomTimestamp,
                'updated_at' => $randomTimestamp,
            ]);

            // Generate transaction
            Transactions::create([
                'user_id' => $account->id,
                'unique_cv_id' => $cvTemplate->unique_cv_id,
                'invoice_id' => $orderId,
                'order_id' => $orderId,
                'status' => $faker->randomElement(['success', 'pending', 'failed']),
                'created_at' => $randomTimestamp,
                'updated_at' => $randomTimestamp,
            ]);
        }
    }
}
