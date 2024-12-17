<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['full_name', 'dob', 'contact_number', 'status', 'email', 'gender'];

//    public function packages()
//    {
//        return $this->belongsToMany(Package::class, 'client_package');
//    }
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
