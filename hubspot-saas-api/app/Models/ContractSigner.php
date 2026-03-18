<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractSigner extends Model
{
    protected $fillable = [
        'contract_id',
        'name',
        'email',
        'status'
    ];
}
