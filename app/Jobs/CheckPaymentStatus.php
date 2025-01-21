<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Models\Transactions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Invoices;
use App\Models\Inventory;

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction;
    public $orderId;
    public $userId;

    /**
     * Create a new job instance.
     *
     * @param Transactions $transaction
     * @param string $orderId
     * @param int $userId
     * @return void
     */
    public function __construct(Transactions $transaction, $orderId, $userId)
    {
        $this->transaction = $transaction;
        $this->orderId = $orderId;
        $this->userId = $userId;  // Store the user ID
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $startTime = time();

        while (true) {
            try {
                // Send GET request to check payment status from Midtrans API
                $response = $client->request('GET', 'https://api.sandbox.midtrans.com/v2/' . $this->orderId . '/status', [
                    'headers' => [
                        'accept' => 'application/json',
                        'authorization' => 'Basic U0ItTWlkLXNlcnZlci16NXlLcmtsM2tZQzN6QkdqdWtoazI2a186', // Use your actual key
                    ],
                ]);

                $responseBody = json_decode($response->getBody(), true);
                // Check if the response is successful and if the payment is 'settlement'
                if (isset($responseBody['transaction_status']) && $responseBody['transaction_status'] == 'settlement') {
                    $invoiceId = uniqid('INV-');
                    // Now update the transaction status
                    $this->transaction->update([
                        'invoice_id' => $invoiceId,
                        'status' => 'paid',
                    ]);

                    // Create the invoice first, then mark the transaction as 'paid'
                    Invoices::create([
                        'username' => User::find($this->userId)->username,
                        'invoice_id' => $invoiceId,
                        'order_id' => $this->orderId,
                        'status' => 'paid',
                        'amount' => $responseBody['gross_amount'] ?? '0',
                        'item_id' => $this->transaction->unique_cv_id,
                    ]);

                    // Retrieve user by ID
                    $user = User::find($this->userId);
                    if ($user) {
                        Log::info('User append started');

                        $inventory = Inventory::where('UID', $user->UID)->first();

                        if ($inventory) {
                            $ownedTemplate = json_decode($inventory->available_items, true) ?? [];
                            Log::info('On owned template');

                            // Append the new item to the owned template
                            $ownedTemplate['available_items'][] = $this->transaction->unique_cv_id;

                            // Update and save the inventory
                            $inventory->available_items = json_encode($ownedTemplate);
                            $inventory->save();

                            Log::info('Updated inventory: ' . json_encode($ownedTemplate));
                            Log::info('Payment successful, transaction marked as paid.');
                        } else {
                            Log::error('Inventory not found for UID: ' . $user->UID);
                        }
                    } else {
                        Log::error('User not found');
                    }


                    // Mark the transaction as 'paid'
                    $this->transaction->status = 'paid';
                    $this->transaction->save();

                    break;
                }

            } catch (\Exception $e) {
                Log::error('Error checking payment status', ['message' => $e->getMessage()]);
            }

            if (time() - $startTime >= 60) {
                Log::info('Payment status check expired.');
                break;
            }

            sleep(rand(1, 2));  // Random delay between 1 and 2 seconds
        }

        Log::info('Payment status check finished.');
    }
}
