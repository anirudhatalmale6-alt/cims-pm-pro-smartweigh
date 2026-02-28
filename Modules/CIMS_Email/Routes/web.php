<?php

use Illuminate\Support\Facades\Route;
use Modules\CIMS_Email\Http\Controllers\EmailController;

// Dashboard / Inbox
Route::get('/', [EmailController::class, 'index'])->name('index');

// Compose
Route::get('compose', [EmailController::class, 'compose'])->name('compose');
Route::post('send', [EmailController::class, 'send'])->name('send');

// Drafts
Route::get('drafts', [EmailController::class, 'drafts'])->name('drafts');
Route::post('save-draft', [EmailController::class, 'saveDraft'])->name('save-draft');

// Sent
Route::get('sent', [EmailController::class, 'sent'])->name('sent');

// View email
Route::get('{id}/view', [EmailController::class, 'view'])->name('view');

// Delete / Trash
Route::delete('{id}/delete', [EmailController::class, 'delete'])->name('delete');
Route::post('{id}/trash', [EmailController::class, 'trash'])->name('trash');

// Templates
Route::get('templates', [EmailController::class, 'templates'])->name('templates');
Route::post('templates/store', [EmailController::class, 'storeTemplate'])->name('templates.store');
Route::put('templates/{id}', [EmailController::class, 'updateTemplate'])->name('templates.update');
Route::delete('templates/{id}', [EmailController::class, 'deleteTemplate'])->name('templates.delete');
Route::get('templates/{id}/load', [EmailController::class, 'loadTemplate'])->name('templates.load');

// AJAX
Route::get('ajax/client-contacts/{clientId}', [EmailController::class, 'getClientContacts'])->name('ajax.client-contacts');
