<?php

use Illuminate\Support\Facades\Route;
use Modules\CIMSDocManager\Http\Controllers\DocManagerController;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DocManagerController::class, 'index'])->name('cimsdocmanager.index');
    Route::get('/create', [DocManagerController::class, 'create'])->name('cimsdocmanager.create');
    Route::post('/', [DocManagerController::class, 'store'])->name('cimsdocmanager.store');
    Route::get('/types/{categoryId}', [DocManagerController::class, 'getTypesByCategory']);
    Route::get('/clients/search', [DocManagerController::class, 'searchClients']);

    Route::get('/view/{document}', [DocManagerController::class, 'view'])->name('cimsdocmanager.view');
    Route::get('/view/client/{client_id}/{document}', [DocManagerController::class, 'view_client'])->name('cimsdocmanager.view.client');

    Route::get('/{id}', [DocManagerController::class, 'show'])->where('id', '[0-9]+')->name('cimsdocmanager.show');
    Route::get('/{id}/edit', [DocManagerController::class, 'edit'])->where('id', '[0-9]+')->name('cimsdocmanager.edit');
    Route::put('/{id}', [DocManagerController::class, 'update'])->where('id', '[0-9]+')->name('cimsdocmanager.update');
    Route::delete('/{id}', [DocManagerController::class, 'destroy'])->where('id', '[0-9]+')->name('cimsdocmanager.destroy');
    Route::get('/{id}/download', [DocManagerController::class, 'download'])->where('id', '[0-9]+')->name('cimsdocmanager.download');
});
