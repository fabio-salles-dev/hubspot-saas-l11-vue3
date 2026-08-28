<?php

namespace App\Services;

use App\Models\HubspotToken;
use App\Models\HubspotSnapshot;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HubspotService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.hubapi.com/',
            'timeout'  => 30,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | OAUTH
    |--------------------------------------------------------------------------
    */

    /**
     * Troca o authorization code pelos tokens do HubSpot.
     */
    public function exchangeCodeForToken(string $code): void
    {
        $response = $this->client->post(
            'oauth/2026-03/token',
            [
                'form_params' => [
                    'grant_type' => 'authorization_code',

                    'client_id' => config(
                        'services.hubspot.client_id'
                    ),

                    'client_secret' => config(
                        'services.hubspot.client_secret'
                    ),

                    'redirect_uri' => config(
                        'services.hubspot.redirect'
                    ),

                    'code' => $code,
                ],
            ]
        );

        $data = json_decode(
            $response->getBody()->getContents(),
            true
        );

        if (
            empty($data['access_token']) ||
            empty($data['refresh_token'])
        ) {
            throw new \RuntimeException(
                'HubSpot não retornou os tokens esperados.'
            );
        }

        HubspotToken::updateOrCreate(
            [
                'id' => 1,
            ],
            [
                'access_token' => $data['access_token'],

                'refresh_token' => $data['refresh_token'],

                'expires_at' => now()->addSeconds(
                    $data['expires_in'] ?? 1800
                ),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica se existe token salvo.
     */
    public function hasValidToken(): bool
    {
        return HubspotToken::query()
            ->whereNotNull('access_token')
            ->whereNotNull('refresh_token')
            ->exists();
    }

    /**
     * Retorna um access token válido.
     *
     * Se estiver expirado, tenta renovar automaticamente.
     */
    public function getValidToken(): string
    {
        $token = HubspotToken::first();

        if (!$token) {
            throw new \RuntimeException(
                'Nenhum token do HubSpot encontrado.'
            );
        }

        if (
            !$token->expires_at ||
            Carbon::parse($token->expires_at)
                ->subMinute()
                ->isPast()
        ) {
            $this->refreshToken($token);

            $token->refresh();
        }

        if (empty($token->access_token)) {
            throw new \RuntimeException(
                'Access token do HubSpot não disponível.'
            );
        }

        return $token->access_token;
    }

    /**
     * Atualiza o access token usando o refresh token.
     */
    private function refreshToken(
        HubspotToken $token
    ): void {
        if (empty($token->refresh_token)) {
            throw new \RuntimeException(
                'Refresh token do HubSpot não disponível.'
            );
        }

        try {

            $response = $this->client->post(
                'oauth/2026-03/token',
                [
                    'form_params' => [
                        'grant_type' => 'refresh_token',

                        'client_id' => config(
                            'services.hubspot.client_id'
                        ),

                        'client_secret' => config(
                            'services.hubspot.client_secret'
                        ),

                        'refresh_token' =>
                            $token->refresh_token,
                    ],
                ]
            );

            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );

            if (empty($data['access_token'])) {
                throw new \RuntimeException(
                    'HubSpot não retornou um novo access token.'
                );
            }

            $token->update([
                'access_token' => $data['access_token'],

                'expires_at' => now()->addSeconds(
                    $data['expires_in'] ?? 1800
                ),

                /*
                 * A API pode retornar um novo refresh token.
                 * Se não retornar, mantém o atual.
                 */
                'refresh_token' =>
                    $data['refresh_token']
                    ?? $token->refresh_token,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Erro ao renovar token do HubSpot',
                [
                    'message' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REQUISIÇÕES
    |--------------------------------------------------------------------------
    */

    /**
     * Executa uma requisição GET autenticada no HubSpot.
     */
    private function get(
        string $endpoint,
        array $query = []
    ): array {
        try {

            $response = $this->client->get(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' =>
                            'Bearer ' . $this->getValidToken(),

                        'Content-Type' =>
                            'application/json',

                        'Accept' =>
                            'application/json',
                    ],

                    'query' => $query,
                ]
            );

            return json_decode(
                $response->getBody()->getContents(),
                true
            ) ?? [];

        } catch (RequestException $e) {

            $response = $e->getResponse();

            $body = $response
                ? $response->getBody()->getContents()
                : null;

            Log::error(
                'Erro HTTP na API do HubSpot',
                [
                    'endpoint' => $endpoint,

                    'status' =>
                        $response
                            ? $response->getStatusCode()
                            : null,

                    'response' => $body,

                    'message' => $e->getMessage(),
                ]
            );

            throw $e;

        } catch (\Throwable $e) {

            Log::error(
                'Erro na requisição para o HubSpot',
                [
                    'endpoint' => $endpoint,

                    'message' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Executa uma requisição POST autenticada no HubSpot.
     */
    private function post(
        string $endpoint,
        array $data = []
    ): array {
        try {

            $response = $this->client->post(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' =>
                            'Bearer ' . $this->getValidToken(),

                        'Content-Type' =>
                            'application/json',

                        'Accept' =>
                            'application/json',
                    ],

                    'json' => $data,
                ]
            );

            return json_decode(
                $response->getBody()->getContents(),
                true
            ) ?? [];

        } catch (RequestException $e) {

            $response = $e->getResponse();

            $body = $response
                ? $response->getBody()->getContents()
                : null;

            Log::error(
                'Erro HTTP POST na API do HubSpot',
                [
                    'endpoint' => $endpoint,

                    'status' =>
                        $response
                            ? $response->getStatusCode()
                            : null,

                    'response' => $body,

                    'message' => $e->getMessage(),
                ]
            );

            throw $e;

        } catch (\Throwable $e) {

            Log::error(
                'Erro no POST para o HubSpot',
                [
                    'endpoint' => $endpoint,

                    'message' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINAÇÃO
    |--------------------------------------------------------------------------
    */

    /**
     * Busca todos os registros de um objeto do CRM.
     *
     * Isso evita o problema de buscar somente os primeiros 100 registros.
     *
     * Exemplo:
     *
     * getAllObjects('contacts', [...])
     * getAllObjects('companies', [...])
     * getAllObjects('deals', [...])
     */
    private function getAllObjects(
        string $object,
        array $properties = []
    ): array {
        $results = [];

        $after = null;

        do {

            $query = [
                'limit' => 100,
            ];

            if (!empty($properties)) {
                $query['properties'] =
                    implode(',', $properties);
            }

            if ($after !== null) {
                $query['after'] = $after;
            }

            $data = $this->get(
                "crm/v3/objects/{$object}",
                $query
            );

            $pageResults =
                $data['results'] ?? [];

            foreach ($pageResults as $item) {
                $results[] = $item;
            }

            $after =
                $data['paging']['next']['after']
                ?? null;

        } while ($after !== null);

        return $results;
    }

    /*
    |--------------------------------------------------------------------------
    | CONTATOS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna todos os contatos do HubSpot.
     *
     * A paginação é automática.
     */
    public function getContacts(): array
    {
        return $this->getAllObjects(
            'contacts',
            [
                'firstname',
                'lastname',
                'email',
                'phone',
                'jobtitle',
                'company',
                'city',
                'state',
                'country',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EMPRESAS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna todas as empresas do HubSpot.
     *
     * A paginação é automática.
     */
    public function getCompanies(): array
    {
        return $this->getAllObjects(
            'companies',
            [
                'name',
                'domain',
                'phone',
                'city',
                'state',
                'country',
                'industry',
                'numberofemployees',
                'annualrevenue',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NEGÓCIOS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna todos os negócios do HubSpot.
     *
     * A paginação é automática.
     */
    public function getDeals(): array
    {
        return $this->getAllObjects(
            'deals',
            [
                'dealname',
                'amount',
                'closedate',
                'createdate',
                'dealstage',
                'pipeline',
                'description',
                'hubspot_owner_id',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONTAGEM DE OBJETOS
    |--------------------------------------------------------------------------
    */

    /**
     * Conta registros usando a Search API.
     *
     * Isso é mais eficiente do que baixar todos os registros
     * somente para descobrir a quantidade.
     */
    private function countObjects(
        string $type
    ): int {
        try {

            $response = $this->post(
                "crm/v3/objects/{$type}/search",
                [
                    'limit' => 1,
                ]
            );

            return (int) (
                $response['total'] ?? 0
            );

        } catch (\Throwable $e) {

            Log::error(
                "Erro ao contar {$type} no HubSpot",
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return 0;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT INFO
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna informações básicas da conta.
     */
    public function getAccountInfo(): ?array
    {
        return $this->getAccountOverview();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT OVERVIEW
    |--------------------------------------------------------------------------
    */

    /**
     * Busca informações da conta diretamente do HubSpot.
     *
     * Também salva um snapshot diário no banco.
     */
    public function getAccountOverview(): ?array
    {
        if (!$this->hasValidToken()) {
            return null;
        }

        try {

            /*
             * Informações da conta.
             */
            $data = $this->get(
                'account-info/v3/details'
            );

            /*
             * Monta overview.
             */
            $overview = [
                'portal_id' =>
                    $data['portalId'] ?? null,

                'company_name' =>
                    $data['accountName']
                    ?? 'DevNest',

                'region' =>
                    $data['dataCenterRegion']
                    ?? 'na1',

                'timezone' =>
                    $data['timeZone']
                    ?? 'UTC',

                'objects' => [
                    'contacts' =>
                        $this->countObjects(
                            'contacts'
                        ),

                    'companies' =>
                        $this->countObjects(
                            'companies'
                        ),

                    'deals' =>
                        $this->countObjects(
                            'deals'
                        ),
                ],
            ];

            /*
             * Salva snapshot diário.
             */
            if (!empty($overview['portal_id'])) {

                HubspotSnapshot::updateOrCreate(
                    [
                        'portal_id' =>
                            $overview['portal_id'],

                        'snapshot_date' =>
                            now()->toDateString(),
                    ],
                    [
                        'company_name' =>
                            $overview['company_name'],

                        'region' =>
                            $overview['region'],

                        'timezone' =>
                            $overview['timezone'],

                        'contacts' =>
                            $overview['objects']['contacts'],

                        'companies' =>
                            $overview['objects']['companies'],

                        'deals' =>
                            $overview['objects']['deals'],
                    ]
                );
            }

            return $overview;

        } catch (\Throwable $e) {

            Log::error(
                'Erro no overview do HubSpot',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PORTAL ID
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o Portal ID do HubSpot.
     */
    public function getPortalId(): ?int
    {
        $overview =
            $this->getAccountOverview();

        return $overview['portal_id']
            ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | DISCONNECT
    |--------------------------------------------------------------------------
    */

    /**
     * Remove os tokens salvos localmente.
     */
    public function disconnect(): void
    {
        HubspotToken::truncate();
    }
}
