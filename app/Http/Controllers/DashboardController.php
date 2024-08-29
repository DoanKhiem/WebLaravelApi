<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'customers' => Customer::count(),
                'packages' => Package::count(),
                'loans' => Loan::count(),
                'members' => User::count()
            ]
        ]);
    }
}
