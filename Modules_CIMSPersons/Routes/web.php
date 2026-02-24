<?php

use Illuminate\Support\Facades\Route;
use Modules\CIMSPersons\Http\Controllers\PersonController;

/*
|--------------------------------------------------------------------------
| CIMSPersons Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('persons')->group(function () {
    Route::get('/', [PersonController::class, 'index'])->name('cimspersons.index');
    Route::get('/search', [PersonController::class, 'search'])->name('cimspersons.search');
    Route::get('/create', [PersonController::class, 'create'])->name('cimspersons.create');
    Route::post('/', [PersonController::class, 'store'])->name('cimspersons.store');
    Route::get('/{id}', [PersonController::class, 'show'])->name('cimspersons.show');
    Route::get('/{id}/edit', [PersonController::class, 'edit'])->name('cimspersons.edit');
    Route::put('/{id}', [PersonController::class, 'update'])->name('cimspersons.update');
    Route::delete('/{id}', [PersonController::class, 'destroy'])->name('cimspersons.destroy');

    // AJAX endpoints
    Route::post('/check-duplicate', [PersonController::class, 'checkDuplicate'])->name('cimspersons.checkDuplicate');
    Route::get('/banks/list', [PersonController::class, 'banks'])->name('cimspersons.banks');
    Route::get('/{id}/banks', [PersonController::class, 'personBanks'])->name('cimspersons.personBanks');
    Route::post('/{id}/banks', [PersonController::class, 'addBank'])->name('cimspersons.addBank');
    Route::delete('/{id}/banks/{bankId}', [PersonController::class, 'removeBank'])->name('cimspersons.removeBank');
    Route::get('/{id}/addresses', [PersonController::class, 'personAddresses'])->name('cimspersons.personAddresses');
    Route::post('/{id}/addresses', [PersonController::class, 'addAddress'])->name('cimspersons.addAddress');
    Route::delete('/{id}/addresses/{linkId}', [PersonController::class, 'removeAddress'])->name('cimspersons.removeAddress');
    Route::get('/addresses/search', [PersonController::class, 'searchAddresses'])->name('cimspersons.searchAddresses');

    Route::get('ajax/person/{id}', [PersonController::class, 'get_person'])->name('cimspersons.ajax.person.get');
});
