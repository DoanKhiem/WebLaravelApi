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
        'fn2_date',
        'fn2_amount',
        'fn3_date',
        'fn3_amount',
        'fn4_date',
        'fn4_amount',
        'fn5_date',
        'fn5_amount',
        'fn6_date',
        'fn6_amount',
        'outstanding_amount',
    ];
}
