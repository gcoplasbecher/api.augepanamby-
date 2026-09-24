<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui são registradas as rotas da API. Todas as rotas deste arquivo
| recebem automaticamente o prefixo /api.
|
*/

Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('api.leads.store');
