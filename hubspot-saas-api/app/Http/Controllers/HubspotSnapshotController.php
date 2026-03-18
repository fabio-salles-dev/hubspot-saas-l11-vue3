<?php

namespace App\Http\Controllers;

use App\Services\HubspotService;
use App\Services\HubspotSnapshotService;
use App\Models\HubspotSnapshot;
use Illuminate\Pipeline\Hub;

class HubspotSnapshotController extends Controller
{
    public function __construct(
        protected HubspotService $hubspotService,
        protected HubspotSnapshotService $hubspotSnapshotService
    ) {}


    public function store(
        HubspotService $hubspot,
        HubspotSnapshotService $snapshotService
    ) {
        $overview = $hubspot->getAccountOverview();

        if (!$overview) {
            return response()->json([
                'error' => 'HubSpot não conectado'
            ], 401);
        }

        $snapshot = $snapshotService->createFromOverview($overview);

        return response()->json($snapshot);
    }


    public function status()
    {
        if (!$this->hubspotService->hasValidToken()) {
            return response()->json([
                'connected' => false
            ]);
        }

        return response()->json([
            'connected' => true,
            'account' => [
                'portal_id' => $this->hubspotService->getPortalId()
            ]
        ]);
    }


    public function history(HubspotSnapshotService $service)
    {
        $portalId = $this->hubspotService->getAccountOverview()['portal_id'];

        return response()->json(
            $service->getHistory($portalId)
        );
    }


    public function overview(){
   $snapshot = HubspotSnapshot::latest('snapshot_date')->first();

    if (!$snapshot) {
        return response()->json([
            'message' => 'Nenhum snapshot disponível'
        ], 404);
    }

    return response()->json([
        'portal_id' => $snapshot->portal_id,
        'company_name' => $snapshot->company_name,
        'region' => $snapshot->region,
        'timezone' => $snapshot->timezone,
        'objects' => [
            'contacts' => $snapshot->contacts,
            'companies' => $snapshot->companies,
            'deals' => $snapshot->deals,
        ]
    ]);
}}

