# CIMS PM Pro - GrowCRM Module

## Server: smartweigh.co.za

## Module Structure

### Modules/cims_pm_pro (Main Module)
- **Location on server:** `/public_html/application/Modules/cims_pm_pro/`
- Contains: ClientMasterController, 21 Models, Routes, Providers, Blade views
- Route prefix: `cims/pm`
- View namespace: `cims_pm_pro::`

### Modules/CIMSDocManager (Document Manager)
- **Location on server:** `/public_html/application/Modules/CIMSDocManager/`
- Contains: DocManagerController, Document models, Routes, views
- Route prefix: `cims/docmanager`
- Key routes: `cimsdocmanager.view.client`, `cimsdocmanager.view`, `cimsdocmanager.download`

### Modules/CIMSPersons (Routes only)
- **Location on server:** `/public_html/application/Modules/CIMSPersons/Routes/web.php`
- Added route: `cimspersons.ajax.person.get`

### Modules/CIMSAddresses (Routes only)
- **Location on server:** `/public_html/application/Modules/CIMSAddresses/Routes/web.php`
- Added route: `ajax.addresses`

## Config Files

### config/filesystems.php
- **Location on server:** `/public_html/application/config/filesystems.php`
- Public disk root: `BASE_DIR.'/application/storage/app/public'`

### modules_statuses.json
- **Location on server:** `/public_html/application/modules_statuses.json`
- cims_pm_pro: true
- CIMSDocManager: true

## Other Files

### views/documents/view_client.blade.php
- **Location on server:** `/public_html/application/resources/views/documents/view_client.blade.php`

### views/layouts/default.blade.php
- **Location on server:** `/public_html/application/resources/views/layouts/default.blade.php`
- Added CSS link: `<link href="/public/assets/css/style.css" rel="stylesheet">`

### assets/css/style.css
- **Location on server:** `/public_html/public/assets/css/style.css`
- Contains: sd_background_pink, font-18 and other custom classes

### images/
- **Location on server:** `/public_html/images/`
- atp_cims_logo.jpg, user_profile.jpg, pdf.png

## Storage

Files are stored at:
- **App storage:** `/public_html/application/storage/app/public/client_docs/{client_code}/`
- **Web accessible:** `/public_html/storage/client_docs/{client_code}/`
- Both locations must contain the same files for the system to work
- The controller checks `storage_path('app/public/')` for file_exists
- The blade view uses `asset('storage/')` for iframe display

## Key Changes Made (Pointers Only, NO Code Changes)

1. Namespace changes: `App\Models` → `Modules\cims_pm_pro\Models`
2. Namespace changes: `App\Http\Controllers` → `Modules\cims_pm_pro\Http\Controllers`
3. View references: `view('clientmaster.')` → `view('cims_pm_pro::clientmaster.')`
4. @include references: `@include('clientmaster.` → `@include('cims_pm_pro::clientmaster.`
5. DocManager model imports pointed to `Modules\cims_pm_pro\Models\*`
6. Filesystem config: public disk root aligned with standard Laravel path

## Cache

After any changes, clear cache at: `https://smartweigh.co.za/public/clear_cache.php`
