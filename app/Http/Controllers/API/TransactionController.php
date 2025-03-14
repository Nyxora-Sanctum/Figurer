<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use App\Jobs\CheckPaymentStatus;
use App\Models\Template;
use App\Models\Invoices;
use App\Models\inventory;

class TransactionController extends Controller
{
    public function payment(Request $request)
    {
        $orderId = 'ORDER-' . Str::random(10);
        $owned_cv_id = Inventory::where('id', auth()->user()->id)->first()->available_items;
        // Verify if the CV exists in cv_template_data
        $cv = Template::where('unique_cv_id', $request->unique_cv_id)->first();
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
                'gross_amount' => Template::where('unique_cv_id', $request->unique_cv_id)->first()->price,
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
        // Retrieve all transactions sorted by the newest
        $transactions = Transactions::orderBy('created_at', 'desc')->get();

        // Return the transactions
        return response()->json($transactions);
    }

    public function getAllInvoicesByAccountID(Request $request)
    {
        // Retrieve user ID from the access token
        $userId = $request->user()->username;

        // Retrieve invoices by user ID
        $invoices = Invoices::where('username', $userId)->get();

        // If no invoices are found, return an error
        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'No invoices found for this account'], 404);
        }

        // Return the invoices
        return response()->json($invoices);
    }
    
    public function getInvoices(Request $request)
    {
        // Retrieve all invoices
        $invoices = Invoices::all();

        // Return the invoices
        return response()->json($invoices);
    }
    
    public function getinvoicebyid(Request $request, $id)
    {
        // Retrieve the invoice by ID
        $invoice = Invoices::where('invoice_id', $id)->first();

        // If no invoice is found, return an error
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Return the invoice details
        return response()->json($invoice);
    }

    public function completeTransactionByOrderID(Request $request, $order_id){
        $transaction = Transactions::where('order_id', $order_id)->first();
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        $transaction->status = 'paid by admin';
        $transaction->save();
        return response()->json(['message' => 'Transaction marked as paid by admin']);
    }

    public function declineTransactionByOrderID(Request $request, $order_id){
        $transaction = Transactions::where('order_id', $order_id)->first();
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        $transaction->status = 'declined by admin';
        $transaction->save();
        return response()->json(['message' => 'Transaction marked as declined by admin']);
    }

    public function getNewTransactions(Request $request, $latestcount)
    {
        $newTransactions = Transactions::orderBy('created_at', 'desc')
            ->take($latestcount)
            ->get();

        // Append the amount variable from invoices by transaction's order ID
        foreach ($newTransactions as $transaction) {
            $invoice = Invoices::where('order_id', $transaction->order_id)->first();
            if ($invoice) {
                $transaction->amount = $invoice->amount;
            }
        }

        return response()->json($newTransactions);
    }

    function getTotalIncomes(Request $request)
    {
        $incomesPerDay = Invoices::selectRaw('SUM(amount) as total, DAY(created_at) as day')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->toArray();

        $totalIncomes = array_sum($incomesPerDay);

        return response()->json([
            'per_day' => $incomesPerDay,
            'total' => $totalIncomes
        ]);
    }

    function getTotalOrders(Request $request)
    {
        $ordersPerDay = Transactions::selectRaw('COUNT(*) as count, DAY(created_at) as day')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        $totalOrders = array_sum($ordersPerDay);

        return response()->json([
            'per_day' => $ordersPerDay,
            'total' => $totalOrders
        ]);
    }
}
