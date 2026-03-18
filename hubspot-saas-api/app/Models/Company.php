<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Deal;

class Company extends Model
{
    protected $fillable = [
        'name',
        'hubspot_id'
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}

