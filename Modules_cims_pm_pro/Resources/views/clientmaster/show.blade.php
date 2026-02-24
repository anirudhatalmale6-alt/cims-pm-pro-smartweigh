@extends('layouts.default')

@section('title', 'View Client: ' . $client->company_name)
@section('header_title', 'Client Details')

@push('styles')
<style>
.detail-section {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
    border-left: 4px solid #17A2B8;
}
.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}
.section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.section-icon.company { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #2563eb; }
.section-icon.tax { background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%); color: #db2777; }
.section-icon.payroll { background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%); color: #ea580c; }
.section-icon.vat { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #059669; }
.section-icon.contact { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4f46e5; }
.section-icon.address { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #d97706; }
.section-icon.sars { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626; }
.section-icon.bank { background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%); color: #0891b2; }
.section-icon.director { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #9333ea; }
.section-icon.partner { background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%); color: #ec4899; }
.section-icon.audit { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); color: #475569; }
.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.detail-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    width: 40%;
    font-weight: 600;
    color: #64748b;
    font-size: 13px;
}
.detail-value {
    width: 60%;
    color: #1e293b;
    font-weight: 500;
    font-size: 14px;
}
.detail-value.empty {
    color: #94a3b8;
    font-style: italic;
}
.client-code-display {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #fff;
    padding: 16px 24px;
    border-radius: 16px;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 1px;
    display: inline-block;
    margin-bottom: 10px;
    box-shadow: 0 4px 14px rgba(13, 148, 136, 0.4);
}
.company-name-display {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}
.trading-name-display {
    font-size: 16px;
    color: #64748b;
}
.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
}
.status-badge.active {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}
.status-badge.inactive {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}
.btn-edit {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    color: #fff;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.2s ease;
}
.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
    color: #fff;
}
.btn-back {
    background: #f1f5f9;
    border: 2px solid #e2e8f0;
    color: #475569;
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.2s ease;
}
.btn-back:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.audit-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 10px;
    border-left: 3px solid #17A2B8;
}
.audit-action {
    font-weight: 600;
    color: #0d9488;
    text-transform: capitalize;
}
.audit-user {
    color: #64748b;
    font-size: 13px;
}
.audit-date {
    color: #94a3b8;
    font-size: 12px;
}
</style>
@endpush

@php
    // Helper function to safely format dates for display
    function formatDisplayDate($value, $format = 'd M Y') {
        if (empty($value)) return null;
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format($format);
        }
        // Try to parse string as date
        if (is_string($value)) {
            try {
                return \Carbon\Carbon::parse($value)->format($format);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
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
            ['label' =>  'View'],
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
    <!-- Header -->
    <div class="detail-section text-center mb-4">
        <div class="client-code-display">{{ $client->client_code }}</div>
        <div class="company-name-display">{{ $client->company_name }}</div>
        @if($client->trading_name)
            <div class="trading-name-display">Trading as: {{ $client->trading_name }}</div>
        @endif
        <div class="mt-3">
            <span class="status-badge {{ $client->is_active ? 'active' : 'inactive' }}">
                {{ $client->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="detail-section d-flex justify-content-center gap-3 mb-4">
        <a href="{{route('client.index')}}" class="btn btn-back">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to List
        </a>
        <a href="{{route('client.edit', $client->client_id)}}" class="btn btn-edit">
            <i class="fa-solid fa-pen me-2"></i>Edit Client
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Company Details -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon company"><i class="fa-solid fa-building"></i></div>
                    <h5 class="section-title">Company Details</h5>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Company Name</div>
                    <div class="detail-value">{{ $client->company_name }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Trading Name</div>
                    <div class="detail-value {{ !$client->trading_name ? 'empty' : '' }}">{{ $client->trading_name ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Registration Number</div>
                    <div class="detail-value {{ !$client->company_reg_number ? 'empty' : '' }}">{{ $client->company_reg_number ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Registration Date</div>
                    <div class="detail-value {{ !$client->company_reg_date ? 'empty' : '' }}">{{ formatDisplayDate($client->company_reg_date) ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Financial Year End</div>
                    <div class="detail-value {{ !$client->financial_year_end ? 'empty' : '' }}">{{ $client->financial_year_end ?: 'Not set' }}</div>
                </div>
            </div>

            <!-- Tax Registration -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon tax"><i class="fa-solid fa-file-invoice"></i></div>
                    <h5 class="section-title">Income Tax Registration</h5>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tax Number</div>
                    <div class="detail-value {{ !$client->tax_number ? 'empty' : '' }}">{{ $client->tax_number ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Registration Date</div>
                    <div class="detail-value {{ !$client->tax_reg_date ? 'empty' : '' }}">{{ formatDisplayDate($client->tax_reg_date) ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">CIPC Annual Returns</div>
                    <div class="detail-value {{ !$client->cipc_annual_returns ? 'empty' : '' }}">{{ $client->cipc_annual_returns ?: 'Not set' }}</div>
                </div>
            </div>

            <!-- Payroll Registration -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon payroll"><i class="fa-solid fa-wallet"></i></div>
                    <h5 class="section-title">Payroll Registration</h5>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-row">
                            <div class="detail-label">PAYE Number</div>
                            <div class="detail-value {{ !$client->paye_number ? 'empty' : '' }}">{{ $client->paye_number ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">SDL Number</div>
                            <div class="detail-value {{ !$client->sdl_number ? 'empty' : '' }}">{{ $client->sdl_number ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">UIF Number</div>
                            <div class="detail-value {{ !$client->uif_number ? 'empty' : '' }}">{{ $client->uif_number ?: 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-row">
                            <div class="detail-label">Dept Labour Number</div>
                            <div class="detail-value {{ !$client->dept_labour_number ? 'empty' : '' }}">{{ $client->dept_labour_number ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">WCA/COIDA Number</div>
                            <div class="detail-value {{ !$client->wca_coida_number ? 'empty' : '' }}">{{ $client->wca_coida_number ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Liability Date</div>
                            <div class="detail-value {{ !$client->payroll_liability_date ? 'empty' : '' }}">{{ formatDisplayDate($client->payroll_liability_date) ?: 'Not set' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VAT Registration -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon vat"><i class="fa-solid fa-percent"></i></div>
                    <h5 class="section-title">VAT Registration</h5>
                </div>
                <div class="detail-row">
                    <div class="detail-label">VAT Number</div>
                    <div class="detail-value {{ !$client->vat_number ? 'empty' : '' }}">{{ $client->vat_number ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Registration Date</div>
                    <div class="detail-value {{ !$client->vat_reg_date ? 'empty' : '' }}">{{ formatDisplayDate($client->vat_reg_date) ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Return Cycle</div>
                    <div class="detail-value {{ !$client->vat_return_cycle ? 'empty' : '' }}">{{ $client->vat_return_cycle ?: 'Not set' }}</div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon contact"><i class="fa-solid fa-address-card"></i></div>
                    <h5 class="section-title">Contact Information</h5>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-row">
                            <div class="detail-label">Business Phone</div>
                            <div class="detail-value {{ !$client->phone_business ? 'empty' : '' }}">{{ $client->phone_business ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Mobile</div>
                            <div class="detail-value {{ !$client->phone_mobile ? 'empty' : '' }}">{{ $client->phone_mobile ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">WhatsApp</div>
                            <div class="detail-value {{ !$client->phone_whatsapp ? 'empty' : '' }}">{{ $client->phone_whatsapp ?: 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-row">
                            <div class="detail-label">Email</div>
                            <div class="detail-value {{ !$client->email ? 'empty' : '' }}">{{ $client->email ?: 'Not set' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Website</div>
                            <div class="detail-value {{ !$client->website ? 'empty' : '' }}">
                                @if($client->website)
                                    <a href="{{ $client->website }}" target="_blank">{{ $client->website }}</a>
                                @else
                                    Not set
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Director Details -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon director"><i class="fa-solid fa-user-tie"></i></div>
                    <h5 class="section-title">Director Details</h5>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value {{ !$client->director_full_name ? 'empty' : '' }}">{{ $client->director_full_name ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">ID Number</div>
                    <div class="detail-value {{ !$client->director_id_number ? 'empty' : '' }}">{{ $client->director_id_number ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Marital Status</div>
                    <div class="detail-value {{ !$client->director_marital_status ? 'empty' : '' }}">{{ $client->director_marital_status ?: 'Not set' }}</div>
                </div>
            </div>

            <!-- Banking Details -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon bank"><i class="fa-solid fa-building-columns"></i></div>
                    <h5 class="section-title">Banking Details</h5>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Account Holder</div>
                    <div class="detail-value {{ !$client->bank_account_holder ? 'empty' : '' }}">{{ $client->bank_account_holder ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Bank Name</div>
                    <div class="detail-value {{ !$client->bank_name ? 'empty' : '' }}">{{ $client->bank_name ?: 'Not set' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Account Number</div>
                    <div class="detail-value {{ !$client->bank_account_number ? 'empty' : '' }}">{{ $client->bank_account_number ? '****' . substr($client->bank_account_number, -4) : 'Not set' }}</div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Linked Addresses -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon address"><i class="fa-solid fa-location-dot"></i></div>
                    <h5 class="section-title">Linked Addresses</h5>
                </div>
                @forelse($client->addresses as $addr)
                    <div class="audit-item">
                        <div class="audit-action">{{ $addr->address_type }} Address</div>
                        <div class="audit-user">Address ID: {{ $addr->address_id }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No addresses linked</p>
                @endforelse
            </div>

            <!-- Audit Trail -->
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-icon audit"><i class="fa-solid fa-history"></i></div>
                    <h5 class="section-title">Audit Trail</h5>
                </div>
                @forelse($client->audits->take(10) as $audit)
                    <div class="audit-item">
                        <div class="audit-action">{{ str_replace('_', ' ', $audit->action) }}</div>
                        <div class="audit-user">{{ $audit->user->name ?? 'System' }}</div>
                        <div class="audit-date">{{ formatDisplayDate($audit->created_at, 'd M Y H:i') }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No audit records</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
