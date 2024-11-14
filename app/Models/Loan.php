<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';
    protected $fillable = [
        'client_id',
        'document_type',
        'nid_driver_license_number',
        'nid_driver_license_file',
        'work_id_number',
        'work_id_file',
        'selfie',
        'package_id',
        'payment_period',
        'pay_slip_1',
        'pay_slip_2',
        'pay_slip_3',

//        'data_fn',
        'outstanding_amount',
        'total_amount',
        'paid_amount',
        'outstanding_amount',
        'period_date',
        'start_date',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function paymentPeriod()
    {
        return $this->belongsTo(PaymentPeriod::class, 'payment_period');
    }

    public function latestUniqueLoanHistory()
    {
        return $this->hasMany(LoanHistory::class, 'loan_id')
            ->select('loan_history.*')
            ->join(DB::raw('(SELECT MAX(id) as id FROM loan_history GROUP BY loan_id, fn) as latest'), 'loan_history.id', '=', 'latest.id');
    }

}
