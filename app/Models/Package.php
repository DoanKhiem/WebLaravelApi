<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['image', 'title', 'detail', 'percent', 'file'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_package');
    }
}
