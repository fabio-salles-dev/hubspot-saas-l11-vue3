<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'hubspot_id',
        'company_id',
        'workspace_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
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
