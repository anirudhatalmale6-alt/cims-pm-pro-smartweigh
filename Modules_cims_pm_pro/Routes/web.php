<?php

use Illuminate\Support\Facades\Route;
use Modules\cims_pm_pro\Http\Controllers\ClientMasterController;

// Client Master
Route::get('/', [ClientMasterController::class, 'index'])->name('client.index');
Route::get('client/create', [ClientMasterController::class, 'create'])->name('client.create');
Route::post('client/store', [ClientMasterController::class, 'store'])->name('client.store');

// AJAX routes - MUST come before /{id} routes
Route::get('ajax/addresses', [ClientMasterController::class, 'getAddresses'])->name('ajax.addresses');
Route::post('ajax/check-company-name', [ClientMasterController::class, 'checkCompanyName'])->name('ajax.check-company-name');
Route::get('ajax/generate-code', [ClientMasterController::class, 'generateCode'])->name('ajax.generate-code');
Route::get('ajax/get-company-type', [ClientMasterController::class, 'getCompanyTypeByCode'])->name('ajax.get-company-type');
Route::get('ajax/bank/{id}', [ClientMasterController::class, 'get_bank'])->name('ajax.bank.get');
Route::get('ajax/address/{id}', [ClientMasterController::class, 'get_address'])->name('ajax.address.get');
Route::put('ajax/directors/{directorId}', [ClientMasterController::class, 'updateDirector'])->name('ajax.directors.update');
Route::put('ajax/banks/{bankId}', [ClientMasterController::class, 'updateBank'])->name('ajax.banks.update');
Route::delete('ajax/banks/{bankId}', [ClientMasterController::class, 'deleteBank'])->name('ajax.banks.delete');
Route::get('ajax/client/{id}', [ClientMasterController::class, 'get_client'])->name('ajax.client.get');
Route::get('ajax/director/{id}', [ClientMasterController::class, 'get_director'])->name('ajax.director.get');

Route::get('clear/cache', [ClientMasterController::class, 'clear_cache'])->name('clear.cache');

// Routes with {id} parameter - MUST come after specific routes
Route::get('client/{id}', [ClientMasterController::class, 'show'])->name('client.show');
Route::get('client/{id}/edit', [ClientMasterController::class, 'edit'])->name('client.edit');
Route::put('client/update/{id}', [ClientMasterController::class, 'update'])->name('client.update');
Route::delete('client/delete/{id}', [ClientMasterController::class, 'destroy'])->name('client.delete');

// Restore soft-deleted
Route::put('client/{id}/restore', [ClientMasterController::class, 'restore'])->name('client.restore');

// Activate/Deactivate
Route::put('client/{id}/activate', [ClientMasterController::class, 'activate'])->name('client.activate');
Route::put('client/{id}/deactivate', [ClientMasterController::class, 'deactivate'])->name('client.deactivate');

// Duplicate client
Route::get('client/{id}/duplicate', [ClientMasterController::class, 'duplicate'])->name('client.duplicate');

// Audit history
Route::get('client/{id}/audit', [ClientMasterController::class, 'audit'])->name('client.audit');

// Check restore (for duplicate validation)
Route::get('/{id}/check-restore', [ClientMasterController::class, 'checkRestore'])->name('check-restore');

// Address linking
Route::post('/{id}/addresses', [ClientMasterController::class, 'linkAddress'])->name('link-address');
Route::delete('/{id}/addresses/{addressId}', [ClientMasterController::class, 'unlinkAddress'])->name('unlink-address');
