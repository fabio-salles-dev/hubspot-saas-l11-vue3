<?php

// app/Models/HubspotSnapshot.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HubspotSnapshot extends Model
{
    protected $table = 'hubspot_snapshots';

    protected $fillable = [
        'portal_id',
        'company_name',
        'region',
        'timezone',
        'contacts',
        'companies',
        'deals',
        'snapshot_date',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
    ];
}
