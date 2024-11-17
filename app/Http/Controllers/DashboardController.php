<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Cronjob;
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
        $cronJob = Cronjob::where('created_at', '>=', Carbon::now()->startOfDay())->first();
        if ($cronJob) return;

        if ($id === 'all') {
            $loans = Loan::whereIn('status', ['Approved', 'Late'])->get();
            $totalLoanUpdate = 0;
            foreach ($loans as $loan) {
                $totalLoanUpdate += $this->updateLoanCronJob($loan);
            }

        } else {
            $loan = Loan::whereIn('status', ['Approved', 'Late'])->find($id);
            if($loan) {
                $totalLoanUpdate = $this->updateLoanCronJob($loan);
            } else {
                $totalLoanUpdate = 0;
            }
        }

        Cronjob::create([
            'total_loans' => $totalLoanUpdate
        ]);
    }

    public function updateLoanCronJob($loan)
    {
        $periodDate = Carbon::parse($loan->period_date)->startOfDay();
        $today = Carbon::now()->startOfDay();
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
                if (!($loan->current_fn === 2 && $payment_period->percent === 50)) { // nếu fn hiện tại là 2 và % là 50 thì không cộng tiền
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

            return 1;
        }
        return 0;

    }
}
