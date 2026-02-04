<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

// Endpoint de test d'authentification (sans middleware pour faciliter les tests)
Route::post('auth/test', [AuthController::class, 'test']);

// Routes pour les entreprises
Route::get('companies', [CompanyController::class, 'index'])->middleware('verify.api.user');
Route::get('companies/{company}/billings', [CompanyController::class, 'billings'])->middleware('verify.api.user');

// Routes pour les factures
Route::get('billings', [BillingController::class, 'index'])->middleware('verify.api.user');
Route::get('billings/{billing}', [BillingController::class, 'show'])->middleware('verify.api.user');
Route::put('billings/{billing}', [BillingController::class, 'update'])->middleware('verify.api.user');
Route::patch('billings/{billing}', [BillingController::class, 'update'])->middleware('verify.api.user');
