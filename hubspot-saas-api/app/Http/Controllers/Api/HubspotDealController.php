<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HubspotService;

class HubspotDealController extends Controller
{
    public function __construct(
        private HubspotService $hubspot
    ) {}

    /**
     * ============================================================
     * LISTAR NEGÓCIOS
     * ============================================================
     */
    public function index()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'message' => 'HubSpot não conectado'
            ], 401);
        }

        try {

            $deals = $this->hubspot->getDeals();

            return response()->json([
                'success' => true,
                'total'   => count($deals),
                'data'    => $deals,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar negócios no HubSpot',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     * RESUMO DOS NEGÓCIOS
     * ============================================================
     */
    public function summary()
    {
        if (!$this->hubspot->hasValidToken()) {
            return response()->json([
                'message' => 'HubSpot não conectado'
            ], 401);
        }

        try {

            $deals = $this->hubspot->getDeals();

            $totalDeals = count($deals);

            $totalAmount = 0;

            $won = 0;
            $lost = 0;
            $open = 0;

            $byStage = [];

            foreach ($deals as $deal) {

                $properties = $deal['properties'] ?? [];

                /*
                 * Valor do negócio
                 */
                $amount = (float) ($properties['amount'] ?? 0);

                /*
                 * Etapa
                 */
                $stage = $properties['dealstage'] ?? 'unknown';

                /*
                 * Nome
                 */
                $dealName = $properties['dealname'] ?? '-';

                /*
                 * Soma total
                 */
                $totalAmount += $amount;

                /*
                 * Inicializa etapa
                 */
                if (!isset($byStage[$stage])) {
                    $byStage[$stage] = [
                        'count'  => 0,
                        'amount' => 0,
                    ];
                }

                /*
                 * Quantidade
                 */
                $byStage[$stage]['count']++;

                /*
                 * Valor
                 */
                $byStage[$stage]['amount'] += $amount;

                /*
                 * Classificação
                 */
                if ($stage === 'closedwon') {

                    $won++;

                } elseif ($stage === 'closedlost') {

                    $lost++;

                } else {

                    $open++;
                }
            }

            /*
             * Ticket médio
             */
            $averageTicket = $totalDeals > 0
                ? $totalAmount / $totalDeals
                : 0;

            /*
             * Taxa de conversão
             *
             * Negócios ganhos / total de negócios
             */
            $winRate = $totalDeals > 0
                ? ($won / $totalDeals) * 100
                : 0;

            /*
             * Taxa de perda
             */
            $lossRate = $totalDeals > 0
                ? ($lost / $totalDeals) * 100
                : 0;

            /*
             * Valor ganho
             */
            $wonAmount = $byStage['closedwon']['amount'] ?? 0;

            /*
             * Valor perdido
             */
            $lostAmount = $byStage['closedlost']['amount'] ?? 0;

            /*
             * Valor em aberto
             */
            $openAmount = $totalAmount - $wonAmount - $lostAmount;

            return response()->json([

                /*
                 * Indicadores principais
                 */
                'total_deals' => $totalDeals,

                'total_amount' => $totalAmount,

                'average_ticket' => round($averageTicket, 2),

                /*
                 * Quantidades
                 */
                'won' => $won,

                'lost' => $lost,

                'open' => $open,

                /*
                 * Percentuais
                 */
                'win_rate' => round($winRate, 2),

                'loss_rate' => round($lossRate, 2),

                /*
                 * Valores
                 */
                'won_amount' => $wonAmount,

                'lost_amount' => $lostAmount,

                'open_amount' => $openAmount,

                /*
                 * Distribuição por etapa
                 */
                'by_stage' => $byStage,

                /*
                 * Negócios completos
                 */
                'deals' => $deals,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar resumo dos negócios',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
}
