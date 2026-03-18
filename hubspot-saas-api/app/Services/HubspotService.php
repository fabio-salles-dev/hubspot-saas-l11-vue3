<?php

namespace App\Services;

use App\Models\HubspotToken;
use GuzzleHttp\Client;
use Carbon\Carbon;

class HubspotService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.hubapi.com/',
            'timeout'  => 10,
        ]);
    }

    /* ==========================
     |  OAUTH
     ========================== */

    public function exchangeCodeForToken(string $code): void
    {
        $response = $this->client->post('oauth/v1/token', [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.hubspot.client_id'),
                'client_secret' => config('services.hubspot.client_secret'),
                'redirect_uri'  => config('services.hubspot.redirect'),
                'code'          => $code,
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        HubspotToken::updateOrCreate(
            ['id' => 1],
            [
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at'    => now()->addSeconds($data['expires_in']),
            ]
        );
    }

    /* ==========================
     |  TOKEN
     ========================== */

    // public function hasValidToken(): bool
    // {
    //     $token = HubspotToken::first();

    //     return $token && Carbon::parse($token->expires_at)->isFuture();
    // }

    public function hasValidToken(): bool
    {
        return HubspotToken::query()->whereNotNull('access_token')->exists();
    }


    public function getValidToken(): string
    {
        $token = HubspotToken::firstOrFail();

        if (Carbon::parse($token->expires_at)->isPast()) {
            $this->refreshToken($token);
            $token->refresh();
        }

        return $token->access_token;
    }

    private function refreshToken(HubspotToken $token): void
    {
        $response = $this->client->post('oauth/v1/token', [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => config('services.hubspot.client_id'),
                'client_secret' => config('services.hubspot.client_secret'),
                'refresh_token' => $token->refresh_token,
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        $token->update([
            'access_token' => $data['access_token'],
            'expires_at'   => now()->addSeconds($data['expires_in']),
        ]);
    }

    /* ==========================
     |  API CALLS
     ========================== */

    public function getContacts(): array
    {
        $response = $this->client->get('crm/v3/objects/contacts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken(),
            ]
        ]);

        return json_decode($response->getBody(), true)['results'] ?? [];
    }

    public function getAccountInfo(): ?array
    {
        if (!$this->hasValidToken()) {
            return null;
        }

        $response = $this->client->get('account-info/v3/details', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken(),
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'portal_id'    => $data['portalId'] ?? null,
            'account_name' => $data['companyName'] ?? null,
        ];
    }
    private function countObjects(string $type): int
    {
        $response = $this->client->post("crm/v3/objects/{$type}/search", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken(),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'limit' => 1
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['total'] ?? 0;
    }


    public function getAccountOverview(): ?array
    {
        if (!$this->hasValidToken()) {
            return null;
        }

        $response = $this->client->get('account-info/v3/details', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken(),
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'portal_id' => $data['portalId'] ?? null,
            'company_name' => $data['companyName'] ?? null,
            'timezone' => $data['timeZone'] ?? null,
            'region' => $data['dataRegion'] ?? null,

            'objects' => [
                'contacts'  => $this->countObjects('contacts'),
                'companies' => $this->countObjects('companies'),
                'deals'     => $this->countObjects('deals'),
            ]
        ];
    }

    public function getPortalId(): ?int
    {
        $overview = $this->getAccountOverview();
        return $overview['portal_id'] ?? null;
    }




    /* ==========================
     |  DISCONNECT
     ========================== */

    public function disconnect(): void
    {
        HubspotToken::truncate();
    }
}
