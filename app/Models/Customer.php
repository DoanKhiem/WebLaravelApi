<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'avatar', 'telephone', 'status', 'address', 'id_number', 'email'];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'customer_package');
    }
}
