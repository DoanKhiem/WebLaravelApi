<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'amount'];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_package');
    }
}
