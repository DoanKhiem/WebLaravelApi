<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_loan_approved',
    ];
}
