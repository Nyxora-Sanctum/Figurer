<?php

namespace App\Http\Controllers\API\Web;

use App\Http\Controllers\Controller;
use App\Models\accounts;
use App\Models\Invoices;
use App\Models\Transactions;
use App\Models\cv_template_data; // Added Templates model
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    function getTotalUsers(Request $request)
    {
        $usersPerDay = accounts::where('role', 'user')
            ->selectRaw('COUNT(*) as count, DAY(created_at) as day')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        $totalUsers = array_sum($usersPerDay);

        return response()->json([
            'per_day' => $usersPerDay,
            'total' => $totalUsers
        ]);
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

    function getTotalTemplates(Request $request)
    {
        $templatesPerDay = cv_template_data::selectRaw('COUNT(*) as count, DAY(created_at) as day')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        $totalTemplates = array_sum($templatesPerDay);

        return response()->json([
            'per_day' => $templatesPerDay,
            'total' => $totalTemplates
        ]);
    }
}

