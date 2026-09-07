<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'hubspot_id',
        'workspace_id',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
