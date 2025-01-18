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

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction;
    public $orderId;

    /**
     * Create a new job instance.
     *
     * @param Transactions $transaction
     * @param string $orderId
     * @return void
     */
    public function __construct(Transactions $transaction, $orderId)
    {
        $this->transaction = $transaction;
        $this->orderId = $orderId;
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
                    $this->transaction->status = 'paid';
                    $this->transaction->save();
                    Log::info('Payment successful, transaction marked as paid.');
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
