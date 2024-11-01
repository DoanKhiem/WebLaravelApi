<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'fn_pay_amount_1',
        'fn_pay_amount_2',
        'fn_pay_amount_3',
        'next_fn_pay',
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
}
