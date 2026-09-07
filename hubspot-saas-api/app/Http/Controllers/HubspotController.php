<?php

namespace App\Http\Controllers;

use App\Models\HubspotSnapshot;
use App\Services\HubspotService;
use App\Services\HubspotSnapshotService;
use Illuminate\Http\Request;

class HubspotController extends Controller
{
    public function __construct(
        private HubspotService $hubspot,
        private HubspotSnapshotService $snapshotService
    ) {}

    /**
     * ============================================================
     * REDIRECIONAR PARA O HUBSPOT
     * ============================================================
     */
    public function redirectToHubspot()
    {
        $state = csrf_token();

        session([
            'hubspot_oauth_state' => $state
        ]);

        $query = http_build_query([
            'client_id'     => config('services.hubspot.client_id'),
            'scope'         => config('services.hubspot.scopes'),
            'redirect_uri'  => config('services.hubspot.redirect'),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return redirect(
            "https://app.hubspot.com/oauth/authorize?{$query}"
        );
    }

    /**
     * ============================================================
     * CALLBACK DO OAUTH
     * ============================================================
     */
    public function callback(Request $request)
    {
        /*
         * Verifica se recebeu o código.
         */
        if (!$request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization code não recebido'
            ], 400);
        }

        /*
         * Verifica o state.
         */
        if (
            $request->state !==
            session('hubspot_oauth_state')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'State inválido'
            ], 403);
        }

        try {

            /*
             * Troca authorization code por tokens.
             */
            $this->hubspot->exchangeCodeForToken(
                $request->code
            );

            /*
             * Remove o state utilizado.
             */
            session()->forget('hubspot_oauth_state');

            /*
             * Redireciona para o Vue.
             */
            return redirect(
                'http://localhost:5173/hubspot?status=success'
            );

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao conectar com o HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * STATUS DA CONEXÃO
     * ============================================================
     */
    public function status()
    {
        $connected =
            $this->hubspot->hasValidToken();

        return response()->json([
            'success' => true,

            'connected' => $connected,

            'account' => $connected
                ? $this->hubspot->getAccountInfo()
                : null,
        ]);
    }

    /**
     * ============================================================
     * OVERVIEW
     *
     * Retorna o último snapshot salvo no banco.
     * ============================================================
     */
    public function overview()
    {
        $snapshot = HubspotSnapshot::query()
            ->orderByDesc('snapshot_date')
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Nenhum snapshot disponível'
            ], 404);
        }

        return response()->json([
            'success' => true,

            'portal_id' =>
                $snapshot->portal_id,

            'company_name' =>
                $snapshot->company_name,

            'region' =>
                $snapshot->region,

            'timezone' =>
                $snapshot->timezone,

            'snapshot_date' =>
                $snapshot->snapshot_date,

            'objects' => [
                'contacts' =>
                    $snapshot->contacts,

                'companies' =>
                    $snapshot->companies,

                'deals' =>
                    $snapshot->deals,
            ]
        ]);
    }

    /**
     * ============================================================
     * LIVE OVERVIEW
     *
     * Busca os dados diretamente no HubSpot
     * e salva um snapshot diário.
     *
     * ============================================================
     */
    public function liveOverview()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'HubSpot não conectado'
            ], 401);
        }

        try {

            /*
             * Busca os dados diretamente do HubSpot.
             */
            $overview =
                $this->hubspot->getAccountOverview();

            if (!$overview) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Não foi possível obter o overview'
                ], 500);
            }

            /*
             * ====================================================
             * SALVAR SNAPSHOT
             * ====================================================
             *
             * O HubspotSnapshotService utiliza:
             *
             * portal_id + data
             *
             * como chave.
             *
             * Portanto:
             *
             * - primeira chamada do dia = cria
             * - demais chamadas no mesmo dia = atualiza
             * - amanhã = cria novo registro
             *
             * Isso evita duplicar snapshots durante o dia.
             */
            $this->snapshotService
                ->createFromOverview($overview);

            /*
             * Retorna os dados atuais normalmente.
             */
            return response()->json([
                'success' => true,
                ...$overview,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao buscar overview do HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * HISTÓRICO
     *
     * Retorna os snapshots dos últimos 30 dias.
     * ============================================================
     */
    public function history()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'HubSpot não conectado'
            ], 401);
        }

        try {

            /*
             * Busca as informações da conta para descobrir
             * o portal_id atualmente conectado.
             */
            $account =
                $this->hubspot->getAccountInfo();

            $portalId =
                $account['portal_id']
                ?? $account['portalId']
                ?? null;

            if (!$portalId) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Portal ID do HubSpot não encontrado'
                ], 500);
            }

            /*
             * Busca os snapshots dos últimos 30 dias.
             */
            $history =
                $this->snapshotService
                    ->getHistory(
                        (int) $portalId,
                        30
                    );

            return response()->json(
                $history
            );

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao carregar histórico do HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * CONTATOS
     * ============================================================
     */
    public function contacts()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'HubSpot não conectado'
            ], 401);
        }

        try {

            $contacts =
                $this->hubspot->getContacts();

            return response()->json([
                'success' => true,

                'total' =>
                    count($contacts),

                'data' =>
                    $contacts,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao buscar contatos no HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * EMPRESAS
     * ============================================================
     */
    public function companies()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'HubSpot não conectado'
            ], 401);
        }

        try {

            $companies =
                $this->hubspot->getCompanies();

            return response()->json([
                'success' => true,

                'total' =>
                    count($companies),

                'data' =>
                    $companies,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao buscar empresas no HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * NEGÓCIOS
     *
     * Endpoint legado.
     *
     * O endpoint recomendado para negócios é:
     *
     * /api/hubspot/deals
     *
     * através do HubspotDealController.
     * ============================================================
     */
    public function deals()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'HubSpot não conectado'
            ], 401);
        }

        try {

            $deals =
                $this->hubspot->getDeals();

            return response()->json([
                'success' => true,

                'total' =>
                    count($deals),

                'data' =>
                    $deals,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao buscar negócios no HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * DESCONECTAR
     * ============================================================
     */
    public function disconnect()
    {
        try {

            $this->hubspot->disconnect();

            return response()->json([
                'success' => true,
                'message' =>
                    'HubSpot desconectado com sucesso'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Erro ao desconectar o HubSpot',
                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }
}

