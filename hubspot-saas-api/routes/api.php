<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HubspotController;
use App\Http\Controllers\HubspotSnapshotController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\HubspotDealController;

// ================== AUTH USER ==================
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ================== HUBSPOT (PÚBLICO - OAUTH + DASHBOARD) ==================
Route::get('/hubspot/redirect', [HubspotController::class, 'redirectToHubspot']);
Route::get('/hubspot/callback', [HubspotController::class, 'callback']);
    Route::post('/hubspot/disconnect', [HubspotController::class, 'disconnect']);

// 🔓 Dashboard (LIBERADO)
Route::get('/hubspot/status', [HubspotController::class, 'status']);
Route::get('/hubspot/overview-live', [HubspotController::class, 'liveOverview']);
Route::get('/hubspot/history', [HubspotSnapshotController::class, 'history']);
Route::get('/hubspot/deals', [HubspotDealController::class, 'index']);

// ================== HUBSPOT (PROTEGIDO) ==================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/hubspot/import', [HubspotController::class, 'importContacts']);
    Route::post('/hubspot/contact', [HubspotController::class, 'createContact']);
    Route::post('/hubspot/deal', [HubspotController::class, 'createDeal']);
    Route::post('/hubspot/snapshot', [HubspotSnapshotController::class, 'store']);

});

// ================== API LOCAL ==================
Route::apiResource('clients', ClientController::class);
Route::apiResource('companies', CompanyController::class);
Route::apiResource('deals', DealController::class);
