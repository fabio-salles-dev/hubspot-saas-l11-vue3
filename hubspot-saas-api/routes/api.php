<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HubspotController;
use App\Http\Controllers\HubspotSnapshotController;

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\HubspotDealController;


// ============================================================
// AUTH USER
// ============================================================

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// ============================================================
// HUBSPOT
// OAUTH
// ============================================================

// Iniciar conexão OAuth
Route::get(
    '/hubspot/redirect',
    [HubspotController::class, 'redirectToHubspot']
);

// Callback OAuth
Route::get(
    '/hubspot/callback',
    [HubspotController::class, 'callback']
);

// Desconectar
Route::post(
    '/hubspot/disconnect',
    [HubspotController::class, 'disconnect']
);


// ============================================================
// HUBSPOT
// DASHBOARD
// ============================================================

// Status da conexão
Route::get(
    '/hubspot/status',
    [HubspotController::class, 'status']
);

// Overview salvo no banco
Route::get(
    '/hubspot/overview',
    [HubspotController::class, 'overview']
);

// Overview atualizado diretamente do HubSpot
Route::get(
    '/hubspot/overview-live',
    [HubspotController::class, 'liveOverview']
);

// Histórico dos snapshots
Route::get(
    '/hubspot/history',
    [HubspotSnapshotController::class, 'history']
);


// ============================================================
// HUBSPOT
// DADOS DO CRM
// ============================================================

// Contatos
Route::get(
    '/hubspot/contacts',
    [HubspotController::class, 'contacts']
);

// Empresas
Route::get(
    '/hubspot/companies',
    [HubspotController::class, 'companies']
);

// Negócios
Route::get(
    '/hubspot/deals',
    [HubspotDealController::class, 'index']
);

Route::get(
    '/hubspot/deals/summary',
    [HubspotDealController::class, 'summary']
);

// ============================================================
// HUBSPOT
// OPERAÇÕES PROTEGIDAS
// ============================================================

Route::middleware('auth:sanctum')->group(function () {

    // Importar contatos
    Route::post(
        '/hubspot/import',
        [HubspotController::class, 'importContacts']
    );

    // Criar contato
    Route::post(
        '/hubspot/contact',
        [HubspotController::class, 'createContact']
    );

    // Criar negócio
    Route::post(
        '/hubspot/deal',
        [HubspotController::class, 'createDeal']
    );

    // Criar snapshot
    Route::post(
        '/hubspot/snapshot',
        [HubspotSnapshotController::class, 'store']
    );
});


// ============================================================
// API LOCAL
// ============================================================

Route::apiResource(
    'clients',
    ClientController::class
);

Route::apiResource(
    'companies',
    CompanyController::class
);

Route::apiResource(
    'deals',
    DealController::class
);
