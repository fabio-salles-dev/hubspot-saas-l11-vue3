<?php

namespace App\Services;

use App\Models\HubspotSnapshot;
use Carbon\Carbon;

class HubspotSnapshotService
{
    public function __construct(
        protected HubspotService $hubspotService
    ) {}

    public function createFromOverview(array $overview)
    {
        // Atualiza ou cria um snapshot único por portal_id + snapshot_date
        $snapshot = HubspotSnapshot::updateOrCreate(
            [
                'portal_id'     => $overview['portal_id'],
                'snapshot_date' => now()->toDateString(), // Garante 1 snapshot por dia
            ],
            [
                'company_name' => $overview['company_name'],
                'region'       => $overview['region'],
                'timezone'     => $overview['timezone'],
                'contacts'     => $overview['objects']['contacts'] ?? 0,
                'companies'    => $overview['objects']['companies'] ?? 0,
                'deals'        => $overview['objects']['deals'] ?? 0,
            ]
        );

        return $snapshot;
    }



    public function getHistory(int $portalId, int $days = 30)
    {
        return HubspotSnapshot::query()
            ->where('portal_id', $portalId)
            ->where('snapshot_date', '>=', now()->subDays($days))
            ->orderBy('snapshot_date')
            ->get([
                'snapshot_date',
                'contacts',
                'companies',
                'deals'
            ]);
    }
}
