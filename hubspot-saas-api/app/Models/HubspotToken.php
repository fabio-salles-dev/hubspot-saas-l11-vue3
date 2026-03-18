<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HubspotToken extends Model
{
    protected $fillable = ['access_token', 'refresh_token', 'expires_at'];

    protected $dates = ['expires_at'];

    public function isExpired()
    {
        return $this->expires_at->lessThan(Carbon::now());
    }
}
