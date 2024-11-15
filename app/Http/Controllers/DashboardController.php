<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\Package;
use App\Models\PaymentPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'account_login' => Auth::user(),
                'clients' => Client::count(),
                'packages' => Package::count(),
                'loans' => Loan::count(),
                'agents' => User::count()
            ]
        ]);
    }


    public function cronJobLoan(string $id)
    {
        if ($id === 'all') {
            $loans = Loan::whereIn('status', ['Approved', 'Late'])->get();
            foreach ($loans as $loan) {
                $this->updateLoanCronJob($loan);
            }
        } else {
            $loan = Loan::whereIn('status', ['Approved', 'Late'])->find($id);
            if ($loan) $this->updateLoanCronJob($loan);
        }
    }

    public function updateLoanCronJob($loan)
    {
        $periodDate = Carbon::parse($loan->period_date);
        $today = Carbon::now();
        $daysDiff = $today->diffInDays($periodDate, false);
        if ($daysDiff < 0) { // nếu đã qua ngày đó
            $daysDiff = abs($daysDiff);
            $increments = [ 14 => 2, 28 => 3, 42 => 4, 56 => 5, 70 => 6 ];
            $fnIncrement = 0;
            $nextPayDateIncrement = 0;

            foreach ($increments as $days => $increment) {
                if ($daysDiff <= $days) {
                    $fnIncrement = $increment;
                    $nextPayDateIncrement = $days;
                    break;
                }
            }

            if ($fnIncrement > 1) {
                $loan->current_fn = $fnIncrement; // tăng fn hiện tại
                $loan->next_pay_date = $periodDate->copy()->addDays($nextPayDateIncrement); // cập nhật ngày trả tiếp theo

                $package = Package::find($loan->package_id);
                $payment_period = PaymentPeriod::find($loan->payment_period);
                if ($loan->current_fn === 2 && $payment_period->percent === 50) {
                    $loan->total_amount = $loan->outstanding_amount/2;
                } else {
                    $loan->total_amount = $loan->outstanding_amount + (0.5 * $package->amount);
                    $loan->status = 'Late';
                }
                $loan->next_pay_amount = $loan->total_amount - $loan->paid_amount;

                $loan->save(['current_fn', 'next_pay_date', 'total_amount', 'next_pay_amount', 'status']);
            }

            if ($fnIncrement === 0) {
                $loan->status = 'Blocked';
                $loan->save(['status']);
            }
        }

    }
}
