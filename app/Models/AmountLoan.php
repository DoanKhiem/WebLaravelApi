<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmountLoan extends Model
{
    use HasFactory;

    protected $table = 'amount_loans';

    protected $fillable = [
        'loan_id',

        'fn1_date',
        'fn1_amount',
        'fn1_paid_amount',
        'fn1_paid_date',

        'fn2_date',
        'fn2_amount',
        'fn2_paid_amount',
        'fn2_paid_date',

        'fn3_date',
        'fn3_amount',
        'fn3_paid_amount',
        'fn3_paid_date',

        'fn4_date',
        'fn4_amount',
        'fn4_paid_amount',
        'fn4_paid_date',

        'fn5_date',
        'fn5_amount',
        'fn5_paid_amount',
        'fn5_paid_date',

        'fn6_date',
        'fn6_amount',
        'fn6_paid_amount',
        'fn6_paid_date',

        'outstanding_amount',
        'deleted_at'
    ];
}
