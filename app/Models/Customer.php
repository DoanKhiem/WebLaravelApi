<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['full_name', 'dob', 'avatar', 'telephone', 'status', 'address', 'email'];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'customer_package');
    }
}
