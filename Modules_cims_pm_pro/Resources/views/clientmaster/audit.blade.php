@extends('layouts.default')

@section('title', 'Audit History: ' . $client->company_name)
@section('header_title', 'Audit History')

@push('styles')
<style>
.audit-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
    border-left: 4px solid #17A2B8;
}
.audit-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
    border-left: 3px solid #17A2B8;
}
.audit-item.created { border-left-color: #10b981; }
.audit-item.updated { border-left-color: #3b82f6; }
.audit-item.deleted { border-left-color: #ef4444; }
.audit-item.restored { border-left-color: #8b5cf6; }
.audit-item.activated { border-left-color: #10b981; }
.audit-item.deactivated { border-left-color: #f59e0b; }
.audit-action {
    font-weight: 700;
    font-size: 15px;
    text-transform: capitalize;
    color: #1e293b;
}
.audit-meta {
    color: #64748b;
    font-size: 13px;
    margin-top: 4px;
}
.audit-details {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    font-size: 13px;
}
.btn-back {
    background: #f1f5f9;
    border: 2px solid #e2e8f0;
    color: #475569;
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 12px;
}
.btn-back:hover {
    background: #e2e8f0;
    color: #1e293b;
}
</style>
@endpush

@php
    function formatAuditDate($value, $format = 'd M Y H:i') {
        if (empty($value)) return 'N/A';
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format($format);
        }
        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return $value;
        }
    }
@endphp

@section('content')
<div class="container-fluid">
    <x-primary-breadcrumb
        title="Client Master"
        subtitle="Manage all your clients in one place"
        icon="fa-solid fa-users"
        :breadcrumbs="[
            ['label' => '<i class=\'fa fa-home\'></i> CIMS', 'url' => url('/')],
            ['label' => 'Client Master','url' => route('client.index')],
            ['label' =>  'Audit'],
        ]"
    >
        <x-slot:actions>
            <button type="button" class="btn-page-action" onclick="location.reload();" title="Refresh">
                <i class="fa fa-sync-alt"></i>
            </button>
            <a href="{{ route('client.index') }}" class="btn-page-primary">
                <i class="fa fa-list"></i> All Clients
            </a>
        </x-slot:actions>
    </x-primary-breadcrumb>
    <!-- Page Title -->
    {{-- <div class="row page-titles">
        <div class="d-flex align-items-center justify-content-between">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="fs-2" style="color:#000" href="javascript:void(0)">CIMS</a></li>
                <li class="breadcrumb-item"><a class="fs-2" style="color:#17A2B8" href="/cims/clientmaster">Client Master</a></li>
                <li class="breadcrumb-item"><a class="fs-2" style="color:#17A2B8" href="/cims/clientmaster/{{ $client->client_id }}">{{ $client->client_code }}</a></li>
                <li class="breadcrumb-item active"><a class="fs-2" style="color:#009688" href="javascript:void(0)">Audit History</a></li>
            </ol>
            <a href="{{ route('client.index') }}" class="btn btn-back">
                <i class="fa fa-arrow-left me-2"></i> Back to Client
            </a>
        </div>
    </div> --}}

    <div class="audit-card">
        <h4 class="mb-4">
            <i class="fa fa-history me-2 text-info"></i>
            Audit History for {{ $client->company_name }} ({{ $client->client_code }})
        </h4>

        @forelse($audits as $audit)
            <div class="audit-item {{ $audit->action }}">
                <div class="audit-action">
                    <i class="fa fa-{{ $audit->action == 'created' ? 'plus-circle text-success' : ($audit->action == 'updated' ? 'edit text-primary' : ($audit->action == 'deleted' ? 'trash text-danger' : ($audit->action == 'restored' ? 'undo text-purple' : ($audit->action == 'activated' ? 'check-circle text-success' : ($audit->action == 'deactivated' ? 'ban text-warning' : 'history'))))) }} me-2"></i>
                    {{ str_replace('_', ' ', ucfirst($audit->action)) }}
                </div>
                <div class="audit-meta">
                    <i class="fa fa-user me-1"></i> {{ $audit->user->name ?? 'System' }}
                    &nbsp;|&nbsp;
                    <i class="fa fa-calendar me-1"></i> {{ formatAuditDate($audit->created_at) }}
                </div>
                @if($audit->new_values || $audit->old_values)
                    <div class="audit-details">
                        @if($audit->action == 'updated' && $audit->old_values && $audit->new_values)
                            <strong>Changes:</strong>
                            @php
                                $old = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
                                $new = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
                                $changes = [];
                                if (is_array($old) && is_array($new)) {
                                    foreach ($new as $key => $value) {
                                        if (isset($old[$key]) && $old[$key] != $value && !in_array($key, ['updated_at', 'created_at'])) {
                                            $changes[$key] = ['old' => $old[$key], 'new' => $value];
                                        }
                                    }
                                }
                            @endphp
                            @if(count($changes) > 0)
                                <ul class="mb-0 mt-2">
                                    @foreach($changes as $field => $vals)
                                        <li><strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong> "{{ $vals['old'] ?? 'empty' }}" → "{{ $vals['new'] ?? 'empty' }}"</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No field changes recorded</span>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fa fa-history fa-3x mb-3"></i>
                <p>No audit records found for this client.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
