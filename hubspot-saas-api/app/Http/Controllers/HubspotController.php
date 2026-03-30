<?php

namespace App\Http\Controllers;

use App\Services\HubspotService;
//use App\Models\Client;
use App\Models\HubspotSnapshot;
use Illuminate\Http\Request;

class HubspotController extends Controller
{
    public function __construct(
        private HubspotService $hubspot
    ) {}

    public function redirectToHubspot()
    {
        $state = csrf_token();
        session(['hubspot_oauth_state' => $state]);

        $query = http_build_query([
            'client_id'     => config('services.hubspot.client_id'),
            'scope'         => config('services.hubspot.scopes'),
            'redirect_uri'  => config('services.hubspot.redirect'),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return redirect("https://app.hubspot.com/oauth/authorize?{$query}");
    }

    public function callback(Request $request)
    {
        if (!$request->code) {
            return response()->json(['error' => 'Authorization code não recebido'], 400);
        }

        if ($request->state !== session('hubspot_oauth_state')) {
            return response()->json(['error' => 'State inválido'], 403);
        }

        $this->hubspot->exchangeCodeForToken($request->code);

        return redirect('http://localhost:5173/hubspot?status=success');
    }

    public function status()
    {
        return response()->json([
            'connected' => $this->hubspot->hasValidToken(),
            'account'   => $this->hubspot->getAccountOverview(), // Retorna tudo: portal, nome, região, timezone
        ]);
    }

    public function overview()
    {
        $snapshot = HubspotSnapshot::latest('snapshot_date')->first();

        if (!$snapshot) {
            return response()->json(['message' => 'Nenhum snapshot disponível'], 404);
        }

        return response()->json([
            'portal_id'    => $snapshot->portal_id,
            'company_name' => $snapshot->company_name,
            'region'       => $snapshot->region,
            'timezone'     => $snapshot->timezone,
            'objects'      => [
                'contacts'  => $snapshot->contacts,
                'companies' => $snapshot->companies,
                'deals'     => $snapshot->deals,
            ]
        ]);
    }

    public function disconnect()
    {
        $this->hubspot->disconnect();
        return response()->json(['message' => 'HubSpot desconectado com sucesso']);
    }
}
