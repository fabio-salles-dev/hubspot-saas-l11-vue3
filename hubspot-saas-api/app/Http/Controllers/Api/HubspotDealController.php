<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class HubspotDealController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $token = $user->hubspot_token;

        $response = Http::withToken($token)
            ->get('https://api.hubapi.com/crm/v3/objects/deals', [
                'properties' => 'dealname,amount,dealstage,createdate'
            ]);

        $data = $response->json();

        $deals = collect($data['results'] ?? [])->map(function ($deal) {
            return [
                'id' => $deal['id'],
                'title' => $deal['properties']['dealname'] ?? '-',
                'value' => $deal['properties']['amount'] ?? 0,
                'status' => $deal['properties']['dealstage'] ?? '-',
                'created_at' => $deal['properties']['createdate'] ?? null,
            ];
        });

        return response()->json([
            'data' => $deals
        ]);
    }
}
