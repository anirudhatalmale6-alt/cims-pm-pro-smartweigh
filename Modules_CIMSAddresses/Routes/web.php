<?php

use Illuminate\Support\Facades\Route;
use Modules\CIMSAddresses\Http\Controllers\AddressController;
use Modules\CIMSAddresses\Http\Controllers\CacheController;

/*
|--------------------------------------------------------------------------
| CIMS Addresses Module Routes
|--------------------------------------------------------------------------
| Prefix: /cims/addresses
| Name: cimsaddresses.
*/

// List all addresses
Route::get("/", [AddressController::class, "index"])->name("index");

// Search addresses (for AJAX dropdowns)
Route::get("/search", [AddressController::class, "search"])->name("search");

// Create new address
Route::get("/create", [AddressController::class, "create"])->name("create");
Route::post("/", [AddressController::class, "store"])->name("store");

// View single address
Route::get("/{id}", [AddressController::class, "show"])->name("show")->where("id", "[0-9]+");

// Edit address
Route::get("/{id}/edit", [AddressController::class, "edit"])->name("edit")->where("id", "[0-9]+");
Route::put("/{id}", [AddressController::class, "update"])->name("update")->where("id", "[0-9]+");

// Delete address (soft delete)
Route::delete("/{id}", [AddressController::class, "destroy"])->name("destroy")->where("id", "[0-9]+");

// Toggle active status
Route::post("/{id}/toggle", [AddressController::class, "toggle"])->name("toggle")->where("id", "[0-9]+");
Route::get("/{id}/toggle", [AddressController::class, "toggle"])->name("toggle.get")->where("id", "[0-9]+");

// Check for duplicate before restore (AJAX)
Route::get("/{id}/check-restore", [AddressController::class, "checkRestoreDuplicate"])->name("checkRestore")->where("id", "[0-9]+");

// Restore soft-deleted address
Route::post("/{id}/restore", [AddressController::class, "restore"])->name("restore")->where("id", "[0-9]+");

// Permanently delete address
Route::delete("/{id}/force", [AddressController::class, "forceDelete"])->name("forceDelete")->where("id", "[0-9]+");

// AJAX addresses endpoint
Route::get("ajax/addresses", [AddressController::class, "search"])->name("ajax.addresses");

// Clear all caches (shared across all CIMS modules)
Route::get("/clear-cache", [CacheController::class, "clearAll"])->name("clearCache");
