<?php

namespace App\Http\Controllers\api\web;

use App\Http\Controllers\Controller;
use App\Models\accounts;
use Illuminate\Http\Request;

class AdminManageAccountController extends Controller
{
    public function getAllAccounts()
    {
        $accounts = accounts::all();
        return $accounts;
    }
}