<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronjob extends Model
{
    use HasFactory;

    protected $table = 'cronjobs';

    protected $fillable = [
        'total_loans',
    ];
}
