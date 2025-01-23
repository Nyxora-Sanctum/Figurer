<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use App\Jobs\CheckPaymentStatus;
use App\Models\cv_template_data;
use App\Models\Invoices;
use App\Models\inventory;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        $orderId = 'ORDER-' . Str::random(10);
        $owned_cv_id = Inventory::where('id', auth()->user()->id)->first()->available_items;
        // Verify if the CV exists in cv_template_data
        $cv = cv_template_data::where('unique_cv_id', $request->unique_cv_id)->first();
        if (!$cv) {
            return response()->json(['error' => 'CV not found'], 404);
        }
        $ownedTemplates = json_decode($owned_cv_id, true) ?? [];
        if (in_array($request->unique_cv_id, $ownedTemplates)) {
            return response()->json(['error' => 'CV already owned'], 400);
        }
        // Save the transaction with the generated order ID in your database
        $transaction = Transactions::create([
            'user_id' => $request->user()->id,
            'unique_cv_id' => $request->unique_cv_id,
            'order_id' => $orderId,
            'status' => 'pending',
        ]);

        // Prepare payment request data
        $paymentData = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => cv_template_data::where('unique_cv_id', $request->unique_cv_id)->first()->price,
            ],
            'credit_card' => [
                'secure' => true,
            ],
        ];

        $jsonPaymentData = json_encode($paymentData);

        $client = new Client();

        try {
            // Send POST request to Midtrans API
            $response = $client->request('POST', 'https://app.sandbox.midtrans.com/snap/v1/transactions', [
                'body' => $jsonPaymentData,
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic U0ItTWlkLXNlcnZlci16NXlLcmtsM2tZQzN6QkdqdWtoazI2a186',
                    'content-type' => 'application/json',
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);

            // Dispatch the background job to check the payment status
            $user = auth()->user();
            CheckPaymentStatus::dispatch($transaction, $orderId, $user->id);

            // Append the order ID to the response
            $responseBody['order_id'] = $orderId;

            // Return the response
            return response()->json($responseBody);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment request failed', 'message' => $e->getMessage()]);
        }
    }


    public function getTransaction($orderId, Request $request)
    {
        // Retrieve the transaction by order ID
        $transaction = Transactions::where('order_id', $orderId)->first();

        // If no transaction is found, return an error
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Return the transaction details
        return response()->json($transaction);
    }

    public function getAllTransactions(Request $request)
    {
        // Retrieve all transactions
        $transactions = Transactions::all();

        // Return the transactions
        return response()->json($transactions);
    }

    public function getInvoices(Request $request)
    {
        // Retrieve all invoices
        $invoices = Invoices::all();

        // Return the invoices
        return response()->json($invoices);
    }
    
    public function getinvoicesbyid(Request $request, $id)
    {
        // Retrieve the invoice by ID
        $invoice = Invoices::find($id);

        // If no invoice is found, return an error
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Return the invoice details
        return response()->json($invoice);
    }
}
