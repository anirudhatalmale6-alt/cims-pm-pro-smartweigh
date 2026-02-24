@extends('layouts.default')

@section('title', 'Client Master')
@section('header_title', 'Client Master')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* Stats Cards - Bootstrap 4 Card Widget Style */
.stats-row { margin-bottom: 28px; }
.stat-card {
    border-radius: 12px;
    padding: 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 140px;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}
.stat-card.total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.stat-card.active {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
.stat-card.inactive {
    background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
}
.stat-card.deleted {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
}
.stat-card .stat-label {
    font-size: 13px;
    font-weight: 500;
    opacity: 0.9;
    margin-bottom: 8px;
}
.stat-card .stat-number {
    font-size: 36px;
    font-weight: 700;
    margin: 0;
    line-height: 1.1;
}
.stat-card .stat-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.25);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}
.stat-card .stat-badge i { font-size: 10px; }
.stat-card .stat-icon {
    position: absolute;
    right: 18px;
    bottom: 20px;
    font-size: 60px;
    opacity: 0.4;
    text-shadow: 0 2px 10px rgba(0,0,0,0.15);
}
.stat-card .stat-progress {
    margin-top: 15px;
    height: 6px;
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
    overflow: hidden;
    width: 60%;
}
.stat-card .stat-progress-bar {
    height: 100%;
    background: rgba(255,255,255,0.95);
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(255,255,255,0.6);
}

/* Search Box */
.search-box-wrapper { flex: 1; max-width: 600px; margin: 0 20px; }
.search-box-wrapper .input-group {
    border: 2px solid #17A2B8;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 10px rgba(23, 162, 184, 0.1);
}
.search-field-select {
    max-width: 150px;
    border: none !important;
    border-right: 2px solid #e9ecef !important;
    border-radius: 0 !important;
    font-size: 14px;
    font-weight: 500;
    color: #0d3d56;
    background-color: #f8f9fa;
    padding: 12px 15px;
}
.search-field-select:focus { box-shadow: none !important; outline: none !important; }
.search-input {
    border: none !important;
    border-radius: 0 !important;
    padding: 12px 15px;
    font-size: 15px;
}
.search-input:focus { box-shadow: none !important; outline: none !important; }
.btn-search {
    border: none !important;
    border-radius: 0 !important;
    background: #f8f9fa;
    color: #6c757d;
    padding: 12px 18px;
}
.btn-search:hover { background: #e9ecef; color: #dc3545; }

/* Client Cards - Premium Design */
.client-card {
    border: none;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 18px;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
    border-left: 4px solid #17A2B8;
    overflow: hidden;
}
.client-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle at top right, rgba(23, 162, 184, 0.06) 0%, transparent 70%);
    pointer-events: none;
}
.client-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    background: linear-gradient(145deg, #ffffff 0%, #f0f9ff 100%);
}
.client-card.inactive {
    border-left-color: #f59e0b;
    background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 100%);
}
.client-card.deleted-card {
    border-left-color: #ef4444;
    background: linear-gradient(145deg, #fef2f2 0%, #fee2e2 100%);
}
.client-card.dropdown-active { z-index: 1060 !important; position: relative; overflow: visible !important; }
.client-card { overflow: visible; }
.action-dropdown { position: relative; }
.action-dropdown .dropdown-menu { position: absolute !important; }

.client-code {
    color: #0d9488;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.client-code .badge {
    font-size: 10px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.client-code .badge.bg-success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.client-code .badge.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #fff !important; }
.client-code .badge.bg-danger { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.client-name {
    font-size: 17px;
    font-weight: 700;
    color: #1e293b;
    margin: 8px 0 4px 0;
    line-height: 1.3;
}
.client-trading {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}
.client-date {
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}
.client-date i { color: #cbd5e1; }

.info-block {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    transition: all 0.2s ease;
}
.info-block:hover {
    background: rgba(255, 255, 255, 0.95);
    transform: scale(1.02);
}
.info-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.info-icon.tax {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #2563eb;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
}
.info-icon.vat {
    background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
    color: #db2777;
    box-shadow: 0 4px 6px -1px rgba(219, 39, 119, 0.2);
}
.info-icon.contact {
    background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
    color: #ea580c;
    box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.2);
}
.info-icon.director {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
}
.info-label {
    font-size: 10px;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.8px;
    margin-bottom: 2px;
}
.info-value {
    font-weight: 700;
    color: #334155;
    font-size: 14px;
    line-height: 1.2;
}
.info-sub {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

/* Action dropdown */
.action-dropdown .dropdown-toggle {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border: none;
    padding: 10px 14px;
    font-size: 16px;
    color: #475569;
    border-radius: 10px;
    transition: all 0.2s ease;
}
.action-dropdown .dropdown-toggle:hover {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #fff;
    transform: scale(1.05);
}
.action-dropdown .dropdown-toggle::after { display: none; }
.action-dropdown .dropdown-menu {
    z-index: 1050 !important;
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 8px;
    min-width: 180px;
}
.action-dropdown .dropdown-item {
    border-radius: 8px;
    padding: 10px 14px;
    font-weight: 500;
    transition: all 0.15s ease;
}
.action-dropdown .dropdown-item:hover {
    background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
}
.action-dropdown .dropdown-item.text-danger:hover {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

/* Buttons */
.btn-new-client {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    border: none;
    color: #fff;
    padding: 14px 28px;
    font-weight: 700;
    border-radius: 12px;
    font-size: 15px;
    letter-spacing: 0.3px;
    box-shadow: 0 4px 14px rgba(13, 148, 136, 0.4);
    transition: all 0.3s ease;
}
.btn-new-client:hover {
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(13, 148, 136, 0.5);
    color: #fff;
}
.btn-restore {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: #fff;
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 10px;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    transition: all 0.2s ease;
}
.btn-restore:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
    color: #fff;
}
.btn-delete-forever {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: #fff;
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 10px;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    transition: all 0.2s ease;
}
.btn-delete-forever:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    color: #fff;
}
.client-card-hidden { display: none !important; }
.no-results-message { text-align: center; padding: 40px 20px; color: #6c757d; }
.no-results-message i { font-size: 48px; color: #17A2B8; margin-bottom: 15px; display: block; }
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 60px; color: #17A2B8; margin-bottom: 20px; display: block; }

@media (max-width: 991px) {
    .search-box-wrapper { order: 3; max-width: 100%; width: 100%; margin: 0 0 15px 0; }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <x-primary-breadcrumb
        title="Client Master"
        subtitle="Manage all your clients in one place"
        icon="fa-solid fa-users"
        :breadcrumbs="[
            ['label' => '<i class=\'fa fa-home\'></i> CIMS', 'url' => url('/')],
            ['label' => 'Client Master'],
        ]"
    >
        <x-slot:actions>
            <button type="button" class="btn-page-action" onclick="location.reload();" title="Refresh">
                <i class="fa fa-sync-alt"></i>
            </button>
            <a href="{{ route('client.create') }}" class="btn-page-primary">
                <i class="fa fa-plus"></i> New Client
            </a>
        </x-slot:actions>
    </x-primary-breadcrumb>
    <!-- Stats Cards -->
    @php
        $total = $stats['total'] > 0 ? $stats['total'] : 1;
        $activePercent = round(($stats['active'] / $total) * 100, 1);
        $inactivePercent = round(($stats['inactive'] / $total) * 100, 1);
        $deletedPercent = round(($stats['deleted'] / $total) * 100, 1);
    @endphp
    <div class="row stats-row">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card total">
                <div class="stat-label">Total Clients</div>
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-badge"><i class="fa fa-database"></i> 100%</div>
                <i class="fa-solid fa-building stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: 100%"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card active">
                <div class="stat-label">Active Clients</div>
                <div class="stat-number">{{ $stats['active'] }}</div>
                <div class="stat-badge"><i class="fa fa-arrow-up"></i> {{ $activePercent }}%</div>
                <i class="fa-solid fa-circle-check stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: {{ $activePercent }}%"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card inactive">
                <div class="stat-label">Inactive Clients</div>
                <div class="stat-number">{{ $stats['inactive'] }}</div>
                <div class="stat-badge"><i class="fa fa-pause"></i> {{ $inactivePercent }}%</div>
                <i class="fa-solid fa-circle-pause stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: {{ $inactivePercent }}%"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card deleted">
                <div class="stat-label">Deleted Clients</div>
                <div class="stat-number">{{ $stats['deleted'] }}</div>
                <div class="stat-badge"><i class="fa fa-trash"></i> {{ $deletedPercent }}%</div>
                <i class="fa-solid fa-trash-can stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: {{ $deletedPercent }}%"></div></div>
            </div>
        </div>
    </div>

    <!-- Header with Tabs, Search, and Button -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div class="card-tabs mb-3">
            <ul class="nav nav-tabs style-1 font-18" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#AllStatus" role="tab">All Status</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Active" role="tab">Active</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Inactive" role="tab">Inactive</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Deleted" role="tab">Deleted</a></li>
            </ul>
        </div>

        <div class="search-box-wrapper mb-3">
            <div class="input-group">
                <select id="searchField" class="form-select search-field-select">
                    <option value="all">All Fields</option>
                    <option value="company_name">Company Name</option>
                    <option value="client_code">Client Code</option>
                    <option value="trading_name">Trading Name</option>
                    <option value="tax_number">Tax Number</option>
                    <option value="vat_number">VAT Number</option>
                </select>
                <input type="text" id="searchInput" class="form-control search-input" placeholder="Search clients...">
                <button class="btn btn-search" type="button" id="clearSearch"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('client.create') }}" class="btn btn-new-client">+ New Client</a>
        </div>
    </div>

    <!-- Client Cards -->
    <div class="tab-content">
        <!-- All Status Tab -->
        <div class="tab-pane fade active show" id="AllStatus">
            @forelse($clients as $client)
                @include('cims_pm_pro::clientmaster._card', ['client' => $client, 'showStatus' => true])
            @empty
                <div class="client-card">
                    <div class="empty-state">
                        <i class="fa-solid fa-building"></i>
                        <h5>No clients yet</h5>
                        <p class="text-muted">Add your first client to get started.</p>
                        <a href="{{ route('client.create') }}" class="btn btn-new-client">+ Add First Client</a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Active Tab -->
        <div class="tab-pane fade" id="Active">
            @forelse($clients->where('is_active', 1) as $client)
                @include('cims_pm_pro::clientmaster._card', ['client' => $client, 'showStatus' => false])
            @empty
                <div class="client-card"><div class="empty-state"><i class="fa-solid fa-circle-check"></i><h5>No active clients</h5></div></div>
            @endforelse
        </div>

        <!-- Inactive Tab -->
        <div class="tab-pane fade" id="Inactive">
            @forelse($clients->where('is_active', 0) as $client)
                @include('cims_pm_pro::clientmaster._card', ['client' => $client, 'showStatus' => false, 'inactive' => true])
            @empty
                <div class="client-card"><div class="empty-state"><i class="fa-solid fa-circle-pause"></i><h5>No inactive clients</h5></div></div>
            @endforelse
        </div>

        <!-- Deleted Tab -->
        <div class="tab-pane fade" id="Deleted">
            @forelse($deletedClients ?? [] as $client)
                @include('cims_pm_pro::clientmaster._deleted_card', ['client' => $client])
            @empty
                <div class="client-card"><div class="empty-state"><i class="fa-solid fa-trash-can"></i><h5>No deleted clients</h5><p class="text-muted">Deleted clients will appear here for recovery</p></div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Client?',
        text: "The client will be moved to trash. You can restore it later.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

function confirmRestore(id) {
    Swal.fire({
        title: 'Restore Client?',
        text: "This will restore the client back to active status.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('restore-form-' + id).submit();
        }
    });
}

function confirmPermanentDelete(id) {
    Swal.fire({
        title: 'Permanently Delete?',
        text: "This action CANNOT be undone! The client will be gone forever.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete forever!',
        input: 'text',
        inputPlaceholder: 'Type DELETE to confirm',
        inputValidator: (value) => {
            if (value !== 'DELETE') {
                return 'Please type DELETE to confirm';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('force-delete-form-' + id).submit();
        }
    });
}

function confirmActivate(id) {
    Swal.fire({
        title: 'Activate Client?',
        text: "This will set the client status to Active.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, activate!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('activate-form-' + id).submit();
        }
    });
}

function confirmDeactivate(id) {
    Swal.fire({
        title: 'Deactivate Client?',
        text: "This will set the client status to Inactive.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, deactivate!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deactivate-form-' + id).submit();
        }
    });
}

// Dropdown z-index fix
document.addEventListener('show.bs.dropdown', function(e) {
    const card = e.target.closest('.client-card');
    if (card) {
        document.querySelectorAll('.client-card').forEach(c => c.classList.remove('dropdown-active'));
        card.classList.add('dropdown-active');
    }
});

document.addEventListener('hide.bs.dropdown', function(e) {
    const card = e.target.closest('.client-card');
    if (card) card.classList.remove('dropdown-active');
});

// Search functionality
const searchInput = document.getElementById('searchInput');
const searchField = document.getElementById('searchField');
const clearSearch = document.getElementById('clearSearch');

function performSearch() {
    const query = searchInput.value.toLowerCase().trim();
    const field = searchField.value;
    const allCards = document.querySelectorAll('.tab-pane .client-card');

    allCards.forEach(card => {
        if (card.querySelector('.empty-state')) return;

        let shouldShow = false;

        if (query === '') {
            shouldShow = true;
        } else {
            if (field === 'all') {
                const cardText = card.innerText.toLowerCase();
                shouldShow = cardText.includes(query);
            } else {
                let fieldValue = card.dataset[field] || '';
                shouldShow = fieldValue.toLowerCase().includes(query);
            }
        }

        card.classList.toggle('client-card-hidden', !shouldShow);
    });
}

searchInput.addEventListener('input', performSearch);
searchField.addEventListener('change', performSearch);
clearSearch.addEventListener('click', function() {
    searchInput.value = '';
    performSearch();
    searchInput.focus();
});

// SweetAlert2 Popup for Success/Error Messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{!! session('success') !!}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            html: '<div style="font-size: 16px;">{!! addslashes(session('error')) !!}</div>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    @endif
});
</script>
@endpush
