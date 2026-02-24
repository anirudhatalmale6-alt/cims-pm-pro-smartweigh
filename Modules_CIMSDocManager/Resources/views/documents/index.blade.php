@extends('cimsdocmanager::layouts.default')

@section('title', 'Document Manager')
@section('header_title', 'Document Manager')

@push('styles')
<link href="/public/smartdash/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
<link href="/public/smartdash/css/smartdash-forms.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* Page Header / Breadcrumb */
.smartdash-page-header {
    background: linear-gradient(135deg, #17A2B8 0%, #138496 100%);
    border-radius: 12px;
    padding: 20px 28px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.25);
}
.smartdash-page-header .page-title {
    display: flex;
    align-items: center;
    gap: 15px;
}
.smartdash-page-header .page-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.smartdash-page-header .page-title h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0;
    letter-spacing: 0.5px;
}
.smartdash-page-header .page-title p {
    font-size: 13px;
    margin: 4px 0 0 0;
    opacity: 0.9;
}
.smartdash-page-header .page-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}
.smartdash-page-header .page-breadcrumb a {
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: color 0.2s;
}
.smartdash-page-header .page-breadcrumb a:hover { color: #fff; }
.smartdash-page-header .page-breadcrumb .separator { opacity: 0.5; }
.smartdash-page-header .page-breadcrumb .current { font-weight: 700; color: #fff; }
.smartdash-page-header .page-actions { display: flex; gap: 10px; }
.smartdash-page-header .btn-page-action {
    background: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
}
.smartdash-page-header .btn-page-action:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}
.smartdash-page-header .btn-page-primary {
    background: #fff;
    color: #17A2B8;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.smartdash-page-header .btn-page-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    color: #0d3d56;
}

/* Stats Cards */
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
.stat-card.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.current { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.stat-card.expiring { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); }
.stat-card.expired { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
.stat-card .stat-label { font-size: 13px; font-weight: 500; opacity: 0.9; margin-bottom: 8px; }
.stat-card .stat-number { font-size: 36px; font-weight: 700; margin: 0; line-height: 1.1; }
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

/* Document Cards - Premium Design */
.doc-card {
    border: none;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 18px;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
    border-left: 4px solid #17A2B8;
    overflow: visible;
}
.doc-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle at top right, rgba(23, 162, 184, 0.06) 0%, transparent 70%);
    pointer-events: none;
    border-radius: 0 16px 0 0;
    z-index: 0;
}
.doc-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    background: linear-gradient(145deg, #ffffff 0%, #f0f9ff 100%);
}
.doc-card.expiring-card {
    border-left-color: #f59e0b;
    background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 100%);
}
.doc-card.expiring-card::before {
    background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.08) 0%, transparent 70%);
}
.doc-card.expired-card {
    border-left-color: #ef4444;
    background: linear-gradient(145deg, #fef2f2 0%, #fee2e2 100%);
}
.doc-card.expired-card::before {
    background: radial-gradient(circle at top right, rgba(239, 68, 68, 0.08) 0%, transparent 70%);
}
.doc-card.dropdown-active { z-index: 1000 !important; }
.doc-card .dropdown { position: relative; z-index: 10; }
.doc-card .dropdown.show { z-index: 1100; }
.doc-card .dropdown-menu.show { z-index: 1100 !important; position: absolute !important; }

.doc-code {
    color: #0d9488;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.doc-code .badge {
    font-size: 10px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.doc-code .badge.bg-success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.doc-code .badge.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #fff !important; }
.doc-code .badge.bg-danger { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.doc-title {
    font-size: 17px;
    font-weight: 700;
    color: #1e293b;
    margin: 8px 0 4px 0;
    line-height: 1.3;
}
.doc-client {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}
.doc-date {
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}
.doc-date i { color: #cbd5e1; }

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
.info-icon.category {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #2563eb;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
}
.info-icon.period {
    background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
    color: #db2777;
    box-shadow: 0 4px 6px -1px rgba(219, 39, 119, 0.2);
}
.info-icon.expiry {
    background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
    color: #ea580c;
    box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.2);
}
.info-icon.file {
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

/* File type icon large */
.doc-type-icon {
    font-size: 48px;
    opacity: 0.8;
}
.doc-type-icon.pdf { color: #ef4444; }
.doc-type-icon.word { color: #2563eb; }
.doc-type-icon.excel { color: #059669; }
.doc-type-icon.image { color: #8b5cf6; }
.doc-type-icon.other { color: #64748b; }

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

/* New Document Button */
.btn-new-doc {
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
.btn-new-doc:hover {
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(13, 148, 136, 0.5);
    color: #fff;
}

.doc-card-hidden { display: none !important; }
.no-results-message { text-align: center; padding: 40px 20px; color: #6c757d; }
.no-results-message i { font-size: 48px; color: #17A2B8; margin-bottom: 15px; display: block; }
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 60px; color: #17A2B8; margin-bottom: 20px; display: block; }

@media (max-width: 991px) {
    .search-box-wrapper { order: 3; max-width: 100%; width: 100%; margin: 0 0 15px 0; }
    .smartdash-page-header { flex-direction: column; text-align: center; }
    .smartdash-page-header .page-title { flex-direction: column; }
    .smartdash-page-header .page-title h1 { font-size: 22px; }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="smartdash-page-header mb-4">
        <div class="page-title">
            <div class="page-icon">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div>
                <h1>DOCUMENT MANAGER</h1>
                <p>Manage all your client documents in one place</p>
            </div>
        </div>
        <div class="page-breadcrumb">
            <a href="/cims/landing"><i class="fa fa-home"></i> CIMS</a>
            <span class="separator">/</span>
            <span class="current">Document Manager</span>
        </div>
        <div class="page-actions">
            <button type="button" class="btn-page-action" onclick="location.reload();" title="Refresh">
                <i class="fa fa-sync-alt"></i>
            </button>
            <a href="/cims/docmanager/create" class="btn-page-primary">
                <i class="fa fa-plus"></i> Upload Document
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @php
        $total = $stats['total'] > 0 ? $stats['total'] : 1;
        $currentPercent = isset($stats['current']) ? round(($stats['current'] / $total) * 100, 1) : 0;
        $expiringPercent = round(($stats['expiring_soon'] / $total) * 100, 1);
        $expiredPercent = isset($stats['expired']) ? round(($stats['expired'] / $total) * 100, 1) : 0;
    @endphp
    <div class="row stats-row">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card total">
                <div class="stat-label">Total Documents</div>
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-badge"><i class="fa fa-database"></i> 100%</div>
                <i class="fa-solid fa-folder-open stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: 100%"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card current">
                <div class="stat-label">This Month</div>
                <div class="stat-number">{{ $stats['this_month'] }}</div>
                <div class="stat-badge"><i class="fa fa-arrow-up"></i> New</div>
                <i class="fa-solid fa-cloud-upload-alt stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: 70%"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card expiring">
                <div class="stat-label">Expiring Soon</div>
                <div class="stat-number">{{ $stats['expiring_soon'] }}</div>
                <div class="stat-badge"><i class="fa fa-clock"></i> 30 days</div>
                <i class="fa-solid fa-hourglass-half stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: {{ $expiringPercent }}%"></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card expired">
                <div class="stat-label">Expired</div>
                <div class="stat-number">{{ $stats['expired'] ?? 0 }}</div>
                <div class="stat-badge"><i class="fa fa-exclamation"></i> Action</div>
                <i class="fa-solid fa-calendar-xmark stat-icon"></i>
                <div class="stat-progress"><div class="stat-progress-bar" style="width: {{ $expiredPercent }}%"></div></div>
            </div>
        </div>
    </div>

    <!-- Header with Tabs, Search, and Button -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div class="card-tabs mb-3">
            <ul class="nav nav-tabs style-1" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#AllDocs" role="tab">All Documents</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Current" role="tab">Current</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Expiring" role="tab">Expiring Soon</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#Expired" role="tab">Expired</a></li>
            </ul>
        </div>

        <div class="search-box-wrapper mb-3">
            <div class="input-group">
                <select id="searchField" class="form-select search-field-select">
                    <option value="all">All Fields</option>
                    <option value="title">Document Title</option>
                    <option value="client">Client Name</option>
                    <option value="category">Category</option>
                    <option value="code">Document Code</option>
                </select>
                <input type="text" id="searchInput" class="form-control search-input" placeholder="Search documents...">
                <button class="btn btn-search" type="button" id="clearSearch"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <div class="mb-3">
            <a href="/cims/docmanager/create" class="btn btn-new-doc">+ Upload Document</a>
        </div>
    </div>

    <!-- Document Cards -->
    <div class="tab-content">
        <!-- All Documents Tab -->
        <div class="tab-pane fade active show" id="AllDocs">
            @forelse($documents as $doc)
                @include('cimsdocmanager::documents._card', ['doc' => $doc, 'showStatus' => true])
            @empty
                <div class="doc-card">
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <h5>No documents yet</h5>
                        <p class="text-muted">Upload your first document to get started.</p>
                        <a href="/cims/docmanager/create" class="btn btn-new-doc">+ Upload First Document</a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Current Tab -->
        <div class="tab-pane fade" id="Current">
            @php $currentDocs = $documents->filter(fn($d) => !$d->is_expired && ($d->days_until_expiry === null || $d->days_until_expiry > 30)); @endphp
            @forelse($currentDocs as $doc)
                @include('cimsdocmanager::documents._card', ['doc' => $doc, 'showStatus' => false])
            @empty
                <div class="doc-card"><div class="empty-state"><i class="fa-solid fa-circle-check"></i><h5>No current documents</h5></div></div>
            @endforelse
        </div>

        <!-- Expiring Soon Tab -->
        <div class="tab-pane fade" id="Expiring">
            @php $expiringDocs = $documents->filter(fn($d) => !$d->is_expired && $d->days_until_expiry !== null && $d->days_until_expiry <= 30); @endphp
            @forelse($expiringDocs as $doc)
                @include('cimsdocmanager::documents._card', ['doc' => $doc, 'showStatus' => false, 'expiring' => true])
            @empty
                <div class="doc-card"><div class="empty-state"><i class="fa-solid fa-hourglass-half"></i><h5>No expiring documents</h5><p class="text-muted">Great! All documents are up to date.</p></div></div>
            @endforelse
        </div>

        <!-- Expired Tab -->
        <div class="tab-pane fade" id="Expired">
            @php $expiredDocs = $documents->filter(fn($d) => $d->is_expired); @endphp
            @forelse($expiredDocs as $doc)
                @include('cimsdocmanager::documents._card', ['doc' => $doc, 'showStatus' => false, 'expired' => true])
            @empty
                <div class="doc-card"><div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i><h5>No expired documents</h5><p class="text-muted">All documents are within valid dates.</p></div></div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($documents->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $documents->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="/public/smartdash/vendor/sweetalert2/sweetalert2.min.js"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Document?',
        text: "Are you sure you want to delete this document? This action cannot be undone.",
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

// Dropdown z-index fix
document.addEventListener('show.bs.dropdown', function(e) {
    const card = e.target.closest('.doc-card');
    if (card) {
        document.querySelectorAll('.doc-card').forEach(c => c.classList.remove('dropdown-active'));
        card.classList.add('dropdown-active');
    }
});

document.addEventListener('hide.bs.dropdown', function(e) {
    const card = e.target.closest('.doc-card');
    if (card) card.classList.remove('dropdown-active');
});

// Search functionality
const searchInput = document.getElementById('searchInput');
const searchField = document.getElementById('searchField');
const clearSearch = document.getElementById('clearSearch');

function performSearch() {
    const query = searchInput.value.toLowerCase().trim();
    const field = searchField.value;
    const allCards = document.querySelectorAll('.tab-pane .doc-card');

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
                let fieldValue = '';
                switch(field) {
                    case 'title': fieldValue = card.dataset.title || ''; break;
                    case 'client': fieldValue = card.dataset.client || ''; break;
                    case 'category': fieldValue = card.dataset.category || ''; break;
                    case 'code': fieldValue = card.dataset.code || ''; break;
                    default: fieldValue = card.innerText.toLowerCase();
                }
                shouldShow = fieldValue.toLowerCase().includes(query);
            }
        }

        card.classList.toggle('doc-card-hidden', !shouldShow);
    });

    document.querySelectorAll('.tab-pane').forEach(tab => {
        let noResultsMsg = tab.querySelector('.no-results-message');
        let visibleCount = 0;
        tab.querySelectorAll('.doc-card').forEach(c => {
            if (!c.classList.contains('doc-card-hidden') && !c.querySelector('.empty-state')) visibleCount++;
        });

        if (query !== '' && visibleCount === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-message';
                noResultsMsg.innerHTML = '<i class="fa-solid fa-search"></i><h5>No documents found</h5><p>Try a different search term</p>';
                tab.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
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
            text: '{{ session('success') }}',
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
