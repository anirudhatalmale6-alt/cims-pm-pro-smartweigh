@extends('layouts.default')

@php
    // ============================================
    // MODE SETTING - Change to 'production' when going live
    // ============================================
    $mode = 'development'; // Options: 'development' or 'production'
@endphp

@section('title', isset($client) ? 'Edit Client' : 'New Client')

@push('styles')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Client Master Form - Using global smartdash-forms.css styles */
    .client-code-badge {
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        color: #90a4ae;
    }
    .page-titles {
        padding: 1rem 0;
        margin-bottom: 1rem;
    }
</style>

<style>
    
    
</style>
@endpush


@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    
    <x-primary-breadcrumb
        title="Client Master"
        subtitle="Manage all your clients in one place"
        icon="fa-solid fa-users"
        :breadcrumbs="[
            ['label' => '<i class=\'fa fa-home\'></i> CIMS', 'url' => url('/')],
            ['label' => 'Client Master','url' => route('client.index')],
            ['label' =>  isset($client) ? 'Edit' : 'New'],
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

    <div class="row">
        <div class="col-12">
            <div class="card smartdash-form-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fa fa-building"></i>
                        {{ isset($client) ? 'Edit Client: ' . $client->company_name : 'Add New Client' }}
                    </h4>
                    <div class="client-code-badge">
                        {{ isset($client) ? $client->client_code : $clientCode }}
                    </div>
                </div>
                <div class="card-body">
                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- <form action="{{ isset($client) ? '/cims/clientmaster/' . $client->client_id : '/cims/clientmaster' }}" method="POST" id="client_form" enctype="multipart/form-data"> --}}
                    <form action="{{ isset($client) ? route('client.update', $client->client_id) : route('client.store') }}" method="POST" id="client_form" enctype="multipart/form-data">
                        @csrf
                        @if(isset($client))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <!-- SINGLE COLUMN LAYOUT -->
                            <div class="col-12">

                                <!-- Company Information Section -->
                                <div class="form-section-title">
                                    <i class="fa fa-building"></i> COMPANY INFORMATION
                                </div>
                                <!-- Row 1: Registered Company Name (MD7) | Client Code (MD2) | Date of Registration (MD3) -->
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="mb-3">
                                            <label for="company_name" class="form-label">Registered Company Name <span class="text-danger">*</span></label>
                                            <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $client->company_name ?? '') }}" placeholder="As per CIPC records">
                                            <div id="company_name_feedback_success" class="sd_tooltip_green"></div>
                                            <div id="company_name_feedback_error" class="sd_tooltip_red"></div>
                                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="client_code_display" class="form-label">Client Code</label>
                                            <input type="text" id="client_code_display" name="client_code" class="form-control" value="{{ isset($client) ? $client->client_code : '' }}" placeholder="Auto-generated" readonly style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <x-datepicker-previous
                                            name="company_reg_date"
                                            label="Date of Company Registration"
                                            placeholder="dd/mm/yyyy"
                                            value="{{ old('company_reg_date', isset($client->company_reg_date) ? formatDateValue($client->company_reg_date) : '') }}"
                                        />
                                     
                                    </div>
                                </div>
                                <!-- Row 2: Company Registration Number (md3) | Company Type (md3) | BizPortal Number (md3) | Financial Year End (md3) -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="company_reg_number" class="form-label">Company Registration Number</label>
                                            <input type="text" id="company_reg_number" name="company_reg_number" class="form-control sd_highlight" value="{{ old('company_reg_number', $client->company_reg_number ?? '') }}" placeholder="YYYY/NNNNNN/NN" maxlength="15">
                                            <div id="company_reg_feedback" class="sd_tooltip_red"></div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="company_type" class="form-label">Company Type</label>
                                            <input type="text" id="company_type" name="company_type" class="form-control" value="{{ old('company_type', $client->company_type ?? '') }}" readonly style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="bizportal_number" class="form-label">BizPortal Number</label>
                                            <input type="text" id="bizportal_number" name="bizportal_number" class="form-control sd_highlight" value="{{ old('bizportal_number', $client->bizportal_number ?? '') }}" readonly style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="financial_year_end" class="form-label">Financial Year End</label>
                                            <select id="financial_year_end" name="financial_year_end" class="sd_drop_class" style="width: 100%">
                                                @foreach(['February','March','April','May','June','July','August','September','October','November','December','January'] as $month)
                                                    <option value="{{ $month }}" {{ old('financial_year_end', $client->financial_year_end ?? 'February') == $month ? 'selected' : '' }}>{{ $month }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Row 3: Trading Name (md6) | Financial Month (md1) | COR 14.3 Certificate Upload (md5) -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="trading_name" class="form-label">Trading Name</label>
                                            <input type="text" id="trading_name" name="trading_name" class="form-control" value="{{ old('trading_name', $client->trading_name ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="mb-3">
                                            <label for="month_no" class="form-label">AR/BO</label>
                                            <input type="text" id="month_no" name="month_no" class="form-control" value="{{ old('month_no', $client->month_no ?? '') }}" placeholder="02" maxlength="2" readonly style="background-color: #f8f9fa;">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">COR 14.3 Certificate</label>
                                            <input type="file" id="cor_certificate" name="cor_certificate" class="form-control" accept=".pdf">
                                            <small class="text-muted">Upload COR 14.3 Registration Certificate (PDF). Files are versioned.</small>
                                            @if(isset($client) && $client->cor_certificate_uploaded)
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="mt-1">
                                                        <span class="badge bg-success font-18"><i class="fa fa-check"></i> Certificate on file</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary sd_background_pink font-18"> <a href="{{ route('cimsdocmanager.view.client',['client_id' => $client->client_id,'document' => 'cor_14_3_certificate']) }}"  class="text-white" target="_blank"> <i class="fa fa-download"></i> View Certificate </a> </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="number_of_directors" class="form-label">Number of Directors</label>
                                            <input type="number" id="number_of_directors" name="number_of_directors" class="form-control" value="{{ old('number_of_directors', $client->number_of_directors ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="number_of_shares" class="form-label">Number of Shares</label>
                                            <input type="text" id="number_of_shares" name="number_of_shares" class="form-control sd-currency-two currency-input" value="{{ old('number_of_shares', $client->number_of_shares ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-tooltip-selectbox 
                                            name="share_type" 
                                            id="share_type"
                                            label="Share Type"
                                            :options="$share_types"
                                            :selected="old('share_type', $client->share_type_name ?? null)"
                                            :liveSearch="true"
                                            :size="10"
                                        />
                                    </div>
                                </div>

                                <!-- Income Tax Section -->
                                <div class="form-section-title">
                                    <i class="fa fa-file-invoice"></i> Income Tax Registration
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tax_number" class="form-label">Company Income Tax Number</label>
                                            <input type="text" id="tax_number" name="tax_number" class="form-control sd_highlight" value="{{ old('tax_number', $client->tax_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <x-datepicker-previous
                                            name="tax_reg_date"
                                            label="Date of IT Registration"
                                            placeholder="dd/mm/yyyy"
                                            value="{{ old('tax_reg_date', isset($client->tax_reg_date) ? formatDateValue($client->tax_reg_date) : '') }}"
                                        />
                                    </div>

                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">INCOME TAX Notice of Registration</label>
                                            <input type="file" id="income_tax_notice_registration_upload" name="income_tax_notice_registration_upload" class="form-control" accept=".pdf">
                                            <small class="text-muted">Upload INCOME TAX Notice of Registration (PDF). Files are versioned.</small>
                                            @if(isset($client) && $client->income_tax_notice_registration_uploaded)
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="mt-1">
                                                        <span class="badge bg-success font-18"><i class="fa fa-check"></i> Certificate on file</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary sd_background_pink font-18"> <a href="{{ route('cimsdocmanager.view.client',['client_id' => $client->client_id,'document' => 'income_tax_registration']) }}"  class="text-white" target="_blank"> <i class="fa fa-download"></i> View Certificate </a> </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Payroll Section -->
                                <div class="form-section-title">
                                    <i class="fa fa-wallet"></i> Payroll Registration
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="paye_number" class="form-label">PAYE Number</label>
                                            <input type="text" id="paye_number" name="paye_number" class="form-control sd_highlight" value="{{ old('paye_number', $client->paye_number ?? '') }}">
                                            <div id="paye_number_feedback_success" class="sd_tooltip_green"></div>
                                            <div id="paye_number_feedback_error" class="sd_tooltip_red"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="sdl_number" class="form-label">SDL Number</label>
                                            <input type="text" id="sdl_number" name="sdl_number" class="form-control sd_highlight" value="{{ old('sdl_number', $client->sdl_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="uif_number" class="form-label">UIF Number</label>
                                            <input type="text" id="uif_number" name="uif_number" class="form-control sd_highlight" value="{{ old('uif_number', $client->uif_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <x-datepicker-previous
                                            name="payroll_liability_date"
                                            label="Date of Liability"
                                            placeholder="mm/dd/yy"
                                            value="{{ old('payroll_liability_date', isset($client->payroll_liability_date) ? formatDateValue($client->payroll_liability_date) : '') }}"
                                        />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-dropdown-with-tooltip  
                                            id="payroll_status"
                                            :options="[
                                                [
                                                    'value' => 'Active',
                                                    'label' => 'Active',
                                                    'description' => 'Employer is registered for PAYE (and UIF/SDL where applicable) and must submit EMP201 and EMP501 returns.'
                                                ],
                                                [
                                                    'value' => 'Dormant',
                                                    'label' => 'Dormant',
                                                    'description' => 'Employer account is registered but currently not trading or not processing payroll. Returns may still be required unless formally suspended.'
                                                ],
                                                [
                                                    'value' => 'Suspended',
                                                    'label' => 'Suspended',
                                                    'description' => 'Payroll tax account is temporarily restricted due to non-compliance or administrative issues. Submissions or payments may be blocked.'
                                                ],
                                                [
                                                    'value' => 'Deregistered / Cancelled',
                                                    'label' => 'Deregistered / Cancelled',
                                                    'description' => 'Employer registration has been cancelled. No further payroll submissions are required unless reactivated.'
                                                ],
                                                [
                                                    'value' => 'Pending Registration',
                                                    'label' => 'Pending Registration',
                                                    'description' => 'Application submitted to SARS. Employer reference number not yet confirmed.'
                                                ],
                                                [
                                                    'value' => 'Ceased Trading',
                                                    'label' => 'Ceased Trading',
                                                    'description' => 'Employer has stopped trading. Final EMP201/EMP501 submissions must be completed before closure.'
                                                ],
                                                [
                                                    'value' => 'Under Verification / Audit',
                                                    'label' => 'Under Verification / Audit',
                                                    'description' => 'Payroll tax account is under SARS review. Submissions may be subject to additional validation.'
                                                ],
                                            ]"
                                            :selected="'Active'"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <x-dropdown-with-tooltip  
                                            id="emp_201_status"
                                            :options="[
                                                [
                                                    'value' => 'Draft',
                                                    'label' => 'Draft',
                                                    'description' => 'EMP201 has been generated but not yet submitted to SARS.'
                                                ],
                                                [
                                                    'value' => 'Submitted',
                                                    'label' => 'Submitted',
                                                    'description' => 'EMP201 has been successfully submitted to SARS but payment may still be outstanding.'
                                                ],
                                                [
                                                    'value' => 'Payment Basis (SARS Approved)',
                                                    'label' => 'Payment Basis (SARS Approved)',
                                                    'description' => 'VAT is accounted for on a cash basis. Output VAT is declared when payment is received, and input VAT is claimed when suppliers are paid, in line with South African Revenue Service rules.'
                                                ],
                                                [
                                                    'value' => 'Accepted',
                                                    'label' => 'Accepted',
                                                    'description' => 'EMP201 submission has been processed and accepted by SARS.'
                                                ],
                                                [
                                                    'value' => 'Rejected',
                                                    'label' => 'Rejected',
                                                    'description' => 'EMP201 was submitted but rejected by SARS. Corrections are required.'
                                                ],
                                                [
                                                    'value' => 'Payment Pending',
                                                    'label' => 'Payment Pending',
                                                    'description' => 'EMP201 submitted, but PAYE/SDL/UIF payment has not yet been made.'
                                                ],
                                                [
                                                    'value' => 'Paid',
                                                    'label' => 'Paid',
                                                    'description' => 'EMP201 submitted and full payment received by SARS.'
                                                ],
                                                [
                                                    'value' => 'Partially Paid',
                                                    'label' => 'Partially Paid',
                                                    'description' => 'EMP201 submitted, but only part of the amount has been paid.'
                                                ],
                                                [
                                                    'value' => 'Overdue',
                                                    'label' => 'Overdue',
                                                    'description' => 'EMP201 not submitted or paid by the due date (7th of the following month).'
                                                ],
                                                [
                                                    'value' => 'Nil Return',
                                                    'label' => 'Nil Return',
                                                    'description' => 'EMP201 submitted with zero liability for the period.'
                                                ],
                                                [
                                                    'value' => 'Revised',
                                                    'label' => 'Revised',
                                                    'description' => 'EMP201 has been resubmitted to correct a previous declaration.'
                                                ]
                                            ]"
                                            :selected="'Active'"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <x-dropdown-with-tooltip  
                                            id="emp_501_status"
                                            :options="[
                                                [
                                                    'value' => 'Draft',
                                                    'label' => 'Draft',
                                                    'description' => 'EMP501 reconciliation prepared but not yet submitted to SARS.'
                                                ],
                                                [
                                                    'value' => 'Submitted',
                                                    'label' => 'Submitted',
                                                    'description' => 'EMP501 successfully submitted to SARS. Awaiting processing.'
                                                ],
                                                [
                                                    'value' => 'Accepted',
                                                    'label' => 'Accepted',
                                                    'description' => 'EMP501 reconciliation processed and accepted by SARS.'
                                                ],
                                                [
                                                    'value' => 'Rejected',
                                                    'label' => 'Rejected',
                                                    'description' => 'EMP501 rejected by SARS. Errors must be corrected and resubmitted.'
                                                ],
                                                [
                                                    'value' => 'Under Review',
                                                    'label' => 'Under Review',
                                                    'description' => 'EMP501 under SARS verification or audit review.'
                                                ],
                                                [
                                                    'value' => 'Revised',
                                                    'label' => 'Revised',
                                                    'description' => 'EMP501 resubmitted to correct prior discrepancies.'
                                                ],
                                                [
                                                    'value' => 'Overdue',
                                                    'label' => 'Overdue',
                                                    'description' => 'EMP501 not submitted by the reconciliation deadline.'
                                                ],
                                                [
                                                    'value' => 'Balanced',
                                                    'label' => 'Balanced',
                                                    'description' => 'EMP201 declarations reconcile fully with IRP5/IT3(a) certificates and payments.'
                                                ],
                                                [
                                                    'value' => 'Imbalance Detected',
                                                    'label' => 'Imbalance Detected',
                                                    'description' => 'Differences exist between EMP201 totals and IRP5/IT3(a) certificates.'
                                                ],
                                                [
                                                    'value' => 'Penalty Applied',
                                                    'label' => 'Penalty Applied',
                                                    'description' => 'Late submission or imbalance penalties have been raised by SARS'
                                                ]
                                            ]"
                                            :selected="'Active'"
                                        />
                                        <div id="last_vat_return_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="dept_labour_number" class="form-label">Dept of Labour Number</label>
                                            <input type="text" id="dept_labour_number" name="dept_labour_number" class="form-control" value="{{ old('dept_labour_number', $client->dept_labour_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="wca_coida_number" class="form-label">WCA - COIDA Number</label>
                                            <input type="text" id="wca_coida_number" name="wca_coida_number" class="form-control" value="{{ old('wca_coida_number', $client->wca_coida_number ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">PAYROLL Notice of Registration</label>
                                            <input type="file" id="payroll_notice_registration_upload" name="payroll_notice_registration_upload" class="form-control" accept=".pdf">
                                            <small class="text-muted">Upload PAYROLL Notice of Registration (PDF). Files are versioned.</small>
                                            @if(isset($client) && $client->payroll_notice_registration_uploaded)
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="mt-1">
                                                        <span class="badge bg-success font-18"><i class="fa fa-check"></i> Certificate on file</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary sd_background_pink font-18"> <a href="{{ route('cimsdocmanager.view.client',['client_id' => $client->client_id,'document' => 'payroll_registration']) }}"  class="text-white" target="_blank"> <i class="fa fa-download"></i> View Certificate </a> </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- VAT Section -->
                                <div class="form-section-title">
                                    <i class="fa fa-percent"></i> VAT Registration
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="vat_number" class="form-label">VAT Number</label>
                                            <input type="text" id="vat_number" name="vat_number" class="form-control sd_highlight" value="{{ old('vat_number', $client->vat_number ?? '') }}">
                                            <div id="vat_number_feedback_success" class="sd_tooltip_green"></div>
                                            <div id="vat_number_feedback_error" class="sd_tooltip_red"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <x-datepicker-previous
                                        name="vat_reg_date"
                                        label="Date of Registration"
                                        placeholder="dd/mm/yyyy"
                                        value="{{ old('vat_reg_date', isset($client->vat_reg_date) ? formatDateValue($client->vat_reg_date) : '') }}"
                                        />
                                    </div>
                                    <div class="col-md-3">
                                         <x-dropdown-with-tooltip
                                            id="vat_return_cycle"
                                            name="vat_return_cycle"
                                            label="Return Cycle"
                                            :options="[
                                                ['value' => 'Monthly', 'label' => 'Monthly'],
                                                ['value' => 'Bi-Monthly', 'label' => 'Bi-Monthly'],
                                                ['value' => '4-Monthly', 'label' => '4-Monthly'],
                                                ['value' => '6-Monthly', 'label' => '6-Monthly'],
                                                ['value' => 'Annually', 'label' => 'Annually']
                                            ]"
                                            :selected="old('vat_return_cycle', $client->vat_return_cycle ?? null)"
                                        />
                                    </div>
                                    <div class="col-md-3">
                                        <x-datepicker-previous
                                        name="vat_effect_from"
                                        label="With Effect From"
                                        placeholder="dd/mm/yyyy"
                                        value="{{ old('vat_effect_from', isset($client->vat_effect_from) ? formatDateValue($client->vat_effect_from) : '') }}"
                                        />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-dropdown-with-tooltip  
                                            id="vat_status"
                                            :options="[
                                                [
                                                    'value' => 'Active',
                                                    'label' => 'Active',
                                                    'description' => 'The entity is actively registered for VAT with SARS and is required to charge VAT on taxable supplies and submit VAT returns (VAT201).'
                                                ],
                                                [
                                                    'value' => 'De-Registered',
                                                    'label' => 'De-Registered',
                                                    'description' => 'VAT account is deregistered. No VAT may be charged, and no further VAT201 submissions are required.'
                                                ],
                                                [
                                                    'value' => 'Suspended',
                                                    'label' => 'Suspended',
                                                    'description' => 'VAT registration is currently suspended by SARS. The entity may be restricted from filing returns or transacting until the suspension is resolved.'
                                                ]
                                            ]"
                                            :selected="'Active'"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <x-dropdown-with-tooltip  
                                            id="vat_basis"
                                            :options="[
                                                [
                                                    'value' => 'Invoice Basis (Standard)',
                                                    'label' => 'Invoice Basis (Standard)',
                                                    'description' => 'VAT is accounted for on the invoice date. Output VAT is declared when invoices are issued, and input VAT is claimed when supplier invoices are received (as per South African Revenue Service rules).'
                                                ],
                                                [
                                                    'value' => 'Payment Basis (SARS Approved)',
                                                    'label' => 'Payment Basis (SARS Approved)',
                                                    'description' => 'VAT is accounted for on a cash basis. Output VAT is declared when payment is received, and input VAT is claimed when suppliers are paid, in line with South African Revenue Service rules.'
                                                ]
                                            ]"
                                            :selected="'Active'"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <x-datepicker-previous
                                            name="last_vat_return"
                                            label="Last VAT Return"
                                            placeholder="dd/mm/yyyy"
                                        />
                                        <div id="last_vat_return_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-7">
                                         <x-tooltip-selectbox 
                                            name="vat_cycle" 
                                            id="vat_cycle"
                                            label="VAT Cycle"
                                            :options="$vat_cycles"
                                            :selected="old('vat_cycle', $client->vat_cycle_id ?? null)"
                                            :liveSearch="true"
                                            :size="10"
                                        />
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">VAT Registration</label>
                                            <input type="file" id="vat_registration_upload" name="vat_registration_upload" class="form-control" accept=".pdf">
                                            <small class="text-muted">Upload VAT Registration (PDF). Files are versioned.</small>
                                            @if(isset($client) && $client->vat_registration_uploaded)
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="mt-1">
                                                        <span class="badge bg-success font-18"><i class="fa fa-check"></i> Certificate on file</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary sd_background_pink font-18"> <a href="{{ route('cimsdocmanager.view.client',['client_id' => $client->client_id,'document' => 'vat_registration']) }}"  class="text-white" target="_blank"> <i class="fa fa-download"></i> View Certificate </a> </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Section -->
                                <div class="form-section-title">
                                    <i class="fa fa-address-card"></i> Contact Information
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="phone_business" class="form-label">Business Phone</label>
                                            <input type="tel" id="phone_business" name="phone_business" class="form-control sd_highlight" value="{{ old('phone_business', $client->phone_business ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="direct" class="form-label">Direct</label>
                                            <input type="tel" id="direct" name="direct" class="form-control sd_highlight" value="{{ old('direct', $client->direct ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="phone_mobile" class="form-label">Mobile</label>
                                            <input type="tel" id="phone_mobile" name="phone_mobile" class="form-control sd_highlight" value="{{ old('phone_mobile', $client->phone_mobile ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="phone_whatsapp" class="form-label">WhatsApp</label>
                                            <input type="tel" id="phone_whatsapp" name="phone_whatsapp" class="form-control sd_highlight" value="{{ old('phone_whatsapp', $client->phone_whatsapp ?? '') }}">
                                        </div>
                                    </div>

                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address (Compliance) </label>
                                            <input type="email_compliance" id="email_compliance" name="email_compliance" class="form-control @error('email_compliance') is-invalid @enderror" value="{{ old('email_compliance', $client->email ?? '') }}">
                                            @error('email_compliance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address (Admin)</label>
                                            <input type="email_admin" id="email_admin" name="email_admin" class="form-control @error('email_admin') is-invalid @enderror" value="{{ old('email_admin', $client->email_admin ?? '') }}">
                                            @error('email_admin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" id="website" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $client->website ?? '') }}" placeholder="https://www.example.co.za">
                                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <!-- Address Section -->
                                <div class="form-section-title d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fa fa-location-dot"></i> Address Details
                                    </span>
                                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addressModal">
                                        <i class="fa fa-save" style="color:#fff"></i> ADD Address
                                    </button>
                                </div>
                                <div class="row" id="saved-address-list">

                                </div>


                                  <!-- VAT Section -->
                                <div class="form-section-title  d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fa fa-percent"></i> Director Management ( <span id="director_display">{{ old('number_of_directors', $client->number_of_directors ?? '0') }}</span> Directors) (<span id="director_share_display">{{ old('number_of_shares', $client->number_of_shares ?? '0') }}</span> Shares)
                                    </span>
                                    <button type="button" class="btn btn-primary btn-lg" id="add-director">
                                        <i class="fa fa-save" style="color:#fff"></i> ADD Director
                                    </button>
                                </div>
                               <div class="row"  id="saved-director-list">
                                </div>

                                <!-- SARS E-Filing Section -->
                                <div class="form-section-title">
                                    <i class="fa fa-lock"></i> SARS E-Filing Login Details
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sars_login" class="form-label">SARS Login</label>
                                            <input type="text" id="sars_login" name="sars_login" class="form-control" value="{{ old('sars_login', $client->sars_login ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-password-asterisk
                                            name="sars_password"
                                            id="sars_password"
                                            label="SARS Password"
                                            value="{{ $client->sars_password ?? '' }}"
                                            autocomplete="new-password"
                                        />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sars_otp_mobile" class="form-label">Mobile for SARS OTP</label>
                                            <input type="tel" id="sars_otp_mobile" name="sars_otp_mobile" class="form-control" value="{{ old('sars_otp_mobile', $client->sars_otp_mobile ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sars_otp_email" class="form-label">Email for SARS OTP</label>
                                            <input type="email" id="sars_otp_email" name="sars_otp_email" class="form-control" value="{{ old('sars_otp_email', $client->sars_otp_email ?? '') }}">
                                        </div>
                                    </div>
                                </div>



                              

                                <!-- Banking Section -->
                                <div class="form-section-title d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fa fa-building-columns"></i> Banking Details
                                    </span>
                                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#bankModal">
                                        <i class="fa fa-save" style="color:#fff"></i> ADD Bank
                                    </button>
                                </div>
                           

                                <div class="row"  id="saved-bank-list">
                                </div>


                                <!-- Status & Actions -->
                                <div class="form-section-title">
                                    <i class="fa fa-toggle-on"></i> Signature Capture
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-signature-pad-javascript
                                            name="signature_data"
                                            :value="old('signature_data', $client->sign_text ?? '')"
                                            label="Client Signature"
                                            :enableWacom="true"
                                            :enableFallback="true"
                                        />
                                    </div>
                                </div>

                                <!-- Status & Actions -->
                                <div class="form-section-title pt-5">
                                    <i class="fa fa-toggle-on"></i> Status
                                </div>
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $client->is_active ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active Client</label>
                                    </div>
                                </div>

                                <div class="mb-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa fa-save"></i> {{ isset($client) ? 'Update Client' : 'Save Client' }}
                                    </button>
                                    <a href="{{ route('client.index') }}" class="btn btn-danger btn-lg">
                                        <i class="fa fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                

                <div class="modal fade" id="bankModal" tabindex="-1" aria-labelledby="bankModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bankModalLabel">Banking Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row" id="bank-form">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="bank_account_holder" class="form-label">Account Holder</label>
                                        <input type="text" id="bank_account_holder" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $client->bank_account_holder ?? '') }}">
                                        <div id="bank_account_holder_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <div class="border rounded shadow-sm p-1 bg-white d-flex align-items-center justify-content-center" 
                                            style="width: 200px; height: 200px;">
                                            <img id="bank_logo_display" src="{{ $client->bank_logo ?? asset('bank_logo/other_logo.jpg') }}" 
                                                alt="Bank Logo" 
                                                class="img-fluid" 
                                                style="max-height: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <x-tooltip-selectbox 
                                        name="bank_name" 
                                        id="bank_name"
                                        label="Bank Name" 
                                        :options="$banks"
                                        :selected="old('bank_name', $client->bank_name ?? null)"
                                        :liveSearch="true"
                                        :size="12"
                                    />
                                    <div id="bank_name_error" class="sd_tooltip_red"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="bank_account_number" class="form-label">Account Number</label>
                                        <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $client->bank_account_number ?? '') }}">
                                        <div id="bank_account_number_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                
                            
                                <div class="col-md-4">
                                    <x-tooltip-selectbox 
                                        name="bank_account_type" 
                                        id="bank_account_type"
                                        label="Account Type"
                                        :options="[]"
                                        :selected="null"
                                        :liveSearch="true"
                                        :size="10"
                                    />
                                    <div id="bank_account_type_error" class="sd_tooltip_red"></div>
                                </div>
                                <div class="col-md-4">
                                    <x-datepicker-previous
                                        name="bank_account_date_opened"
                                        label="Date Account Opened"
                                        placeholder="dd/mm/yyyy"
                                    />
                                    <div id="bank_account_date_opened_error" class="sd_tooltip_red"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <x-tooltip-selectbox 
                                            name="account_status" 
                                            id="account_status"
                                            label="Account Status"
                                            :options="[]"
                                            :selected="null"
                                            :liveSearch="true"
                                            :size="10"
                                        />
                                        <div id="account_status_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_branch_name" class="form-label">Branch Name</label>
                                        <input type="text" readonly id="bank_branch_name" name="bank_branch_name" class="form-control" value="{{ old('bank_branch_code', $client->bank_branch_code ?? '') }}">
                                        <div id="bank_branch_name_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_branch_code" class="form-label">Branch Code</label>
                                        <input type="text" readonly id="bank_branch_code" name="bank_branch_code" class="form-control" value="{{ old('bank_branch_code', $client->bank_branch_code ?? '') }}">
                                        <div id="bank_branch_code_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_swift_code" class="form-label">Swift Code</label>
                                        <input type="text" readonly id="bank_swift_code" name="bank_swift_code" class="form-control" value="{{ old('bank_swift_code', $client->bank_swift_code ?? '') }}">
                                        <div id="bank_swift_code_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-dropdown-with-tooltip  
                                        id="bank_statement_frequency"
                                        :options="[]"
                                        :selected="null"
                                    />
                                    <div id="bank_statement_frequency_error" class="sd_tooltip_red"></div>
                                </div>

                                <div class="col-md-4">
                                    <x-datepicker-previous
                                        name="statement_cut_off_date"
                                        label="Statement Cut Off Date"
                                        placeholder="dd/mm/yyyy"
                                    />
                                    <div id="statement_cut_off_date_error" class="sd_tooltip_red"></div>
                                </div>

                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Confirmation of Banking</label>
                                        <input type="file" id="confirmation_of_banking_uplaod" name="confirmation_of_banking_uplaod" class="form-control" accept=".pdf">
                                        <small class="text-muted">Upload Confirmation of Banking (PDF). Files are versioned.</small>
                                        @if(isset($client) && $client->confirmation_of_banking_uplaoded)
                                            <div class="mt-1">
                                                <span class="badge bg-success"><i class="fa fa-check"></i> Certificate on file</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div id="confirmation_of_banking_uplaod_error" class="sd_tooltip_red"></div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                class="form-check-input"
                                                id="is_default"
                                                name="is_default"
                                                value="1">
                                            <label class="form-check-label" for="is_default">Default Bank</label>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="bank_logo" id="bank_logo">

                                {{-- <div class="col-md-12 d-flex justify-content-end">
                                    <button type="button" id="save-bank-btn" class="btn btn-primary btn-lg">
                                        <i class="fa fa-save"></i> Save Bank
                                    </button>
                                </div> --}}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <x-cancel-button data-bs-dismiss="modal"></x-cancel-button>
                            <x-save-button id="save-bank-btn"></x-save-button>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addressModalLabel">Address Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-0">
                            <div class="row" id="address-form">
                                <div class="col-md-12">
                                    <div class="form-section-title d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa fa-location-dot"></i> Select Your Address
                                        </div>
                                        <x-pink-button href="{{ route('cimsaddresses.create') }}" target="_blank">
                                            <i class="fa fa-plus" style="color:#fff"></i> ADD Address
                                        </x-pink-button>
                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <x-dropdown-with-tooltip  
                                                id="address_type"
                                                :options="$address_types"
                                                :selected="null"
                                            />
                                            <div id="address_type_error" class="sd_tooltip_red"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative">
                                                <x-dropdown-with-tooltip  
                                                    id="address"
                                                    :options="$addresses"
                                                    :selected="null"
                                                />
                                                <button type="button" id="refresh-addresses-btn" class="btn btn-outline-primary position-absolute top-0 end-0 mb-1 btn-sm refresh-btn" title="Refresh addresses" style="transform: translate(0px, -20px);">
                                                    Reload <i class="fa fa-rotate"></i>
                                                </button>
                                            </div>
                                            <div id="address_error" class="sd_tooltip_red"></div>
                                        </div>
                                    </div>


                                     <!-- Property Details Section -->
                                    <div class="form-section-title">
                                        <i class="fa fa-building"></i> Property Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-4">
                                                <label for="google_address" class="form-label">Google Address (Type to search)</label>
                                                <input type="text"
                                                    id="google_address"
                                                    name="google_address"
                                                    class="form-control"
                                                    autocomplete="off"
                                                    placeholder="Start typing an address..."
                                                    value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="unit_number" class="form-label">Unit No.</label>
                                                <input type="text"
                                                    id="unit_number"
                                                    name="unit_number"
                                                    class="form-control"
                                                    placeholder="e.g. 12A"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="complex_name" class="form-label">Complex / Building Name</label>
                                                <input type="text"
                                                    id="complex_name"
                                                    name="complex_name"
                                                    class="form-control"
                                                    placeholder="e.g. Sunset Apartments"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Street Details Section -->
                                    <div class="form-section-title">
                                        <i class="fa fa-road"></i> Street Address
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="street_number" class="form-label">Street No.</label>
                                                <input type="text"
                                                    id="street_number"
                                                    name="street_number"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="mb-3">
                                                <label for="street_name" class="form-label">Street Name</label>
                                                <input type="text"
                                                    id="street_name"
                                                    name="street_name"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="suburb" class="form-label">Suburb</label>
                                                <input type="text"
                                                    id="suburb"
                                                    name="suburb"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text"
                                                    id="city"
                                                    name="city"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="postal_code" class="form-label">Postal Code</label>
                                                <input type="text"
                                                    id="postal_code"
                                                    name="postal_code"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="province" class="form-label">Province</label>
                                                <input type="text"
                                                    id="province"
                                                    name="province"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="country" class="form-label">Country</label>
                                                <input type="text"
                                                    id="country"
                                                    name="country"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Location Section -->
                                    <div class="form-section-title">
                                        <i class="fa fa-globe-africa"></i> Location Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="municipality" class="form-label">Municipality</label>
                                                <input type="text"
                                                    id="municipality"
                                                    name="municipality"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ward" class="form-label">Ward</label>
                                                <input type="text"
                                                    id="ward"
                                                    name="ward"
                                                    class="form-control"
                                                    placeholder="Enter ward number"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Coordinates Section -->
                                    <div class="form-section-title">
                                        <i class="fa fa-crosshairs"></i> GPS Coordinates
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="latitude" class="form-label">Latitude</label>
                                                <input type="text"
                                                    id="latitude"
                                                    name="latitude"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="longitude" class="form-label">Longitude</label>
                                                <input type="text"
                                                    id="longitude"
                                                    name="longitude"
                                                    class="form-control"
                                                    value=""
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="map_url" class="form-label">Google Map URL</label>
                                        <input type="text"
                                            id="map_url"
                                            name="map_url"
                                            class="form-control"
                                            placeholder="Auto-generated from coordinates"
                                            value=""
                                            readonly>
                                    </div>

                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                class="form-check-input"
                                                id="is_default"
                                                name="is_default"
                                                value="1">
                                            <label class="form-check-label" for="is_default">Default Address</label>
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                        </div>
                        <div class="modal-footer">
                            <x-cancel-button data-bs-dismiss="modal"></x-cancel-button>
                            <x-save-button id="save-address-btn"></x-save-button>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="directorModal" tabindex="-1" aria-labelledby="directorModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="directorModalLabel">Director Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row" id="director-form">
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <div class="border rounded shadow-sm p-1 bg-white d-flex align-items-center justify-content-center" 
                                            style="width: 200px; height: 200px;">
                                            <img id="director_profile_image_display" src="{{ asset('bank_logo/other_logo.jpg') }}" 
                                                alt="Director Profile Logo" 
                                                class="img-fluid" 
                                                style="max-height: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-tooltip-selectbox 
                                                name="person_id" 
                                                id="person_id"
                                                label="Person Name" 
                                                :options="$persons"
                                                :selected="old('person_id')"
                                                :liveSearch="true"
                                                :size="12"
                                            />
                                            <div id="person_id_error" class="sd_tooltip_red"></div>
                                        </div>

                                        <div class="col-md-12">
                                            <x-dropdown-with-tooltip  
                                                id="director_type"
                                                :options="$director_types"
                                                :selected="null"
                                            />
                                            <div id="director_type_error" class="sd_tooltip_red"></div>
                                        </div>

                                    </div>
                                    
                                </div>
                                <div class="col-md-6" id="director-status-wrapper">
                                     <x-dropdown-with-tooltip  
                                        id="director_status"
                                        :options="$director_statuses"
                                        :selected="null"
                                    />
                                    <div id="director_status_error" class="sd_tooltip_red"></div>
                                </div>
                                <div class="col-md-6" id="date-engaged-wrapper">
                                    <x-datepicker-previous
                                        name="date_engaged"
                                        label="Date Engaged"
                                        placeholder="dd/mm/yyyy"
                                    />
                                    <div id="date_engaged_error" class="sd_tooltip_red"></div>
                                </div>
                                <div class="col-md-4" id="number-of-director-shares-wrapper" style="display: none;">
                                    <div class="mb-3">
                                        <label for="number_of_director_shares" class="form-label">Number of Shares</label>
                                        <input type="text" id="number_of_director_shares" name="number_of_director_shares" class="form-control sd-currency-two currency-input" value="{{ old('number_of_director_shares') }}">
                                        <div id="number_of_director_shares_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                 <div class="col-md-4" id="date-resigned-wrapper">
                                    <x-datepicker-range
                                        name="date_resigned"
                                        label="Date Resigned"
                                        placeholder="dd/mm/yyyy"
                                    />
                                    <div id="date_resigned_error" class="sd_tooltip_red"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="identity_number" class="form-label">Identity Number</label>
                                        <input type="text" readonly id="identity_number" name="identity_number" class="form-control" value="{{ old('identity_number') }}">
                                        <div id="identity_number_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="identity_type" class="form-label">Identity Type</label>
                                        <input type="text" readonly id="identity_type" name="identity_type" class="form-control" value="{{ old('identity_type') }}">
                                        <div id="identity_type_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nationality" class="form-label">Nationality</label>
                                        <input type="text" readonly id="nationality" name="nationality" class="form-control" value="{{ old('nationality') }}">
                                        <div id="nationality_error" class="sd_tooltip_red"></div>
                                    </div>
                                </div>
                                <input type="hidden" name="director_profile_image" id="director_profile_image">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <x-cancel-button data-bs-dismiss="modal"></x-cancel-button>
                            <x-save-button id="save-director-btn"></x-save-button>
                        </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
// Mode: {{ $mode }}
var APP_MODE = '{{ $mode }}';

// Initialize Bootstrap-Select on dropdowns with sd_drop_class
$(function() {
    if ($.fn.selectpicker) {
        $('select.sd_drop_class').selectpicker({
            liveSearch: true,
            size: 10
        });
    }
});

// Client ID for edit mode (to exclude from uniqueness check)
var clientId = {{ isset($client) ? $client->client_id : 'null' }};
var isEditMode = {{ isset($client) ? 'true' : 'false' }};

// Title case function for company names - capitalize each word except small words
function formatCompanyName(name) {
    if (!name) return name;
    var smallWords = ['and', 'the', 'of', 'for', 'in', 'on', 'at', 'to', 'a', 'an', 'by', 'with'];
    var words = name.trim().split(/\s+/);
    var result = [];

    for (var i = 0; i < words.length; i++) {
        var word = words[i];
        var lowerWord = word.toLowerCase();

        // If word starts with any bracket, push as-is and skip formatting
        if (/^[\(\{\[]/.test(word)) {
            result.push(word);
            continue;
        }

        // First word always capitalized, small words lowercase (unless first)
        if (i === 0 || smallWords.indexOf(lowerWord) === -1) {
            result.push(lowerWord.charAt(0).toUpperCase() + lowerWord.slice(1));
        } else {
            result.push(lowerWord);
        }
    }

    return result.join(' ');
}

// Trading name - format on blur (same capitalization rules as registered company name)
$('#trading_name').on('blur', function() {
    var $input = $(this);
    var name = $input.val().trim();
    if (name) {
        $input.val(formatCompanyName(name));
    }
});

// COR 14.3 Certificate checkbox - enable/disable file input
$('#cor_certificate_uploaded').on('change', function() {
    var $fileInput = $('#cor_certificate');
    if ($(this).is(':checked')) {
        $fileInput.prop('disabled', false);
    } else {
        $fileInput.prop('disabled', true).val('');
    }
});

// Company name validation - check uniqueness, format, and generate client code on blur
$('#company_name').on('blur', function() {
    var name = $(this).val().trim();
    var $successFeedback = $('#company_name_feedback_success');
    var $errorFeedback = $('#company_name_feedback_error');
    var $input = $(this);

    // Hide both tooltips initially
    $successFeedback.removeClass('show');
    $errorFeedback.removeClass('show');

    if (!name) {
        $('#client_code_display').val('');
        return;
    }

    $.ajax({
        url: `{{ route('ajax.check-company-name') }}`,
        type: 'POST',
        data: {
            name: name,
            exclude_id: clientId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // Update the field with formatted name
            $input.val(response.formatted);
          

            if (response.exists) {
                // Show error tooltip
                $errorFeedback.text('This company name already exists!').addClass('show');
                $successFeedback.removeClass('show');
                $input.addClass('is-invalid');
            } else {
                // Show success tooltip
                $successFeedback.text('Company name is available').addClass('show');
                $errorFeedback.removeClass('show');
                $input.removeClass('is-invalid');

                $("#bank_account_holder").val( response.formatted || '')
                // Hide success message after 3 seconds
                setTimeout(function() {
                    $successFeedback.removeClass('show');
                }, 3000);

                // Generate client code from company name (only for new clients)
                if (!isEditMode) {
                    generateClientCode(response.formatted);
                }
            }
        },
        error: function() {
            console.log('Error checking company name');
        }
    });
});

// Function to generate client code from company name
function generateClientCode(companyName) {
    $.ajax({
        // url: '/cims/clientmaster/ajax/generate-code',
        url: `{{ route('ajax.generate-code') }}`,
        type: 'GET',
        data: {
            company_name: companyName
        },
        success: function(response) {
            $('#client_code_display').val(response.code);
        },
        error: function() {
            console.log('Error generating client code');
        }
    });
}

// ============================================
// COMPANY REGISTRATION NUMBER - Smart Input Mask
// Format: YYYY/NNNNNN/NN
// - YYYY: Year (cannot exceed current year)
// - NNNNNN: 6 digits
// - NN: 2 digits (company type code from ref_company_types)
// ============================================
var currentYear = new Date().getFullYear();

$('#company_reg_number').on('input', function(e) {

    var $input = $(this);
    var $regFeedback = $('#company_reg_feedback');
    var $typeFeedback = $('#company_type_feedback');
    var $companyType = $('#company_type');
    var val = $input.val();

    // Remove any non-digit and non-slash characters
    val = val.replace(/[^\d\/]/g, '');

    // Remove extra slashes - only allow slashes at positions 4 and 11
    var digits = val.replace(/\//g, '');

    // Limit to 12 digits total (4+6+2)
    if (digits.length > 12) {
        digits = digits.substring(0, 12);
    }

    // Build formatted value with auto-slashes
    var formatted = '';
    for (var i = 0; i < digits.length; i++) {
        if (i === 4 || i === 10) {
            formatted += '/';
        }
        formatted += digits[i];
    }

    // Update input value
    $input.val(formatted);

    // Clear feedback and company type while typing
    $regFeedback.removeClass('show');
    $typeFeedback.removeClass('show');
    $companyType.val('').removeClass('sd_highlight');
    $input.removeClass('is-invalid is-valid');

    // Validate year (first 4 digits) - cannot exceed current year
    if (digits.length >= 4) {
        var year = parseInt(digits.substring(0, 4));
        if (year > currentYear) {
            $regFeedback.text('Year cannot be greater than ' + currentYear).addClass('show');
            $input.addClass('is-invalid');
            return;
        }
    }

    // When complete (12 digits = YYYY + NNNNNN + NN), validate company type and generate BizPortal number
    if (digits.length === 12) {
        var typeCode = digits.substring(10, 12);
        validateCompanyType(formatted, typeCode, $companyType, $regFeedback, $typeFeedback, $input);


        // Generate BizPortal number: K + YYYY + NNNNNN
        var bizportalNumber = 'K' + digits.substring(0, 4) + digits.substring(4, 10);
        $('#bizportal_number').val(bizportalNumber);
    } else {
        // Clear BizPortal if registration number is incomplete
        $('#bizportal_number').val('');
    }
});

// Also validate on blur in case user pastes or edits
$('#company_reg_number').on('blur', function() {
    var $input = $(this);
    var $regFeedback = $('#company_reg_feedback');
    var $typeFeedback = $('#company_type_feedback');
    var $companyType = $('#company_type');
    var val = $input.val().trim();

    if (!val) {
        $companyType.val('').removeClass('sd_highlight');
        $regFeedback.removeClass('show');
        $typeFeedback.removeClass('show');
        return;
    }

    // Check format
    if (!/^\d{4}\/\d{6}\/\d{2}$/.test(val)) {
        if (val.length > 0) {
            $regFeedback.text('Invalid format. Use YYYY/NNNNNN/NN').addClass('show');
            $input.addClass('is-invalid');
        }
        return;
    }

    // Validate year
    var year = parseInt(val.substring(0, 4));
    if (year > currentYear) {
        $regFeedback.text('Year cannot be greater than ' + currentYear).addClass('show');
        $input.addClass('is-invalid');
        return;
    }

    // Validate company type code
    var typeCode = val.substring(12, 14);
    validateCompanyType(val, typeCode, $companyType, $regFeedback, $typeFeedback, $input);
});

// Function to validate company type code via AJAX
function validateCompanyType(regNumber, typeCode, $companyType, $regFeedback, $typeFeedback, $input) {
    $.ajax({
        url: `{{ route('ajax.get-company-type') }}`,
        type: 'GET',
        data: { reg_number: regNumber },
        success: function(response) {
            if (response.found) {
                $companyType.val(response.type_name);
                
                $typeFeedback.text('Company type: ' + response.type_name).addClass('show');
                $regFeedback.removeClass('show');
                $input.removeClass('is-invalid').addClass('is-valid');

                // Hide success feedback after 3 seconds
                setTimeout(function() {
                    $typeFeedback.removeClass('show');
                }, 3000);
            } else {
                // Invalid company type code
                $companyType.val('').removeClass('sd_highlight');
                $regFeedback.text('Invalid company type code: ' + typeCode).addClass('show');
                $typeFeedback.removeClass('show');
                $input.addClass('is-invalid').removeClass('is-valid');
            }
        },
        error: function() {
            console.log('Error validating company type');
            $companyType.val('').removeClass('sd_highlight');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Development Mode: Clear cache on load
    @if($mode === 'development')
    $.ajax({
        url: `{{ route('clear.cache') }}`,
        type: 'GET',
        success: function(response) {
            if (typeof CIMS !== 'undefined' && CIMS.notify) {
                CIMS.notify('Cache cleared (Dev Mode)', 'info', 2000);
            }
        },
        error: function() {
            // Silent fail - cache clear is optional
            console.log('Dev Mode: Cache clear endpoint not available');
        }
    });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            @if($mode === 'development')
            html: '<div style="font-size: 16px;">{!! addslashes(session('error')) !!}</div>',
            @else
            text: 'An error occurred. Please try again.',
            @endif
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    @endif

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
});


 // Format date for display (Tue, 10 Jan 2026)
function formatDateDisplay(date) {
    if (!date) return '';
    var d = new Date(date);
    var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

// Format date for database (YYYY-MM-DD)
function formatDateDB(date) {
    if (!date) return '';
    var d = new Date(date);
    var year = d.getFullYear();
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
}


// ============================================
// SMART DATE PICKER - sd_datepicker and sd_datepicker_max
// sd_datepicker: No date restrictions
// sd_datepicker_max: Max date = today (cannot select future dates)
// Display format: "Tue, 10 Jan 2026"
// Database format: "YYYY-MM-DD"
// ============================================
$(function() {
   

    if ($.fn.bootstrapMaterialDatePicker) {
        // Initialize sd_datepicker (no max date restriction)
        $('.sd_datepicker').each(function() {
            var $display = $(this);
            var fieldName = $display.attr('id').replace('_display', '');
            var $hidden = $('#' + fieldName);
            if ($hidden.val()) { $display.val(formatDateDisplay($hidden.val())); }
            $display.bootstrapMaterialDatePicker({
                weekStart: 0, time: false, format: 'ddd, D MMM YYYY', clearButton: true
            }).on('change', function(e, date) {
                $hidden.val(date ? formatDateDB(date) : '');
            });
        });

        // Initialize sd_datepicker_max (max date = today)
        $('.sd_datepicker_max').each(function() {
            var $display = $(this);
            var fieldName = $display.attr('id').replace('_display', '');
            var $hidden = $('#' + fieldName);
            if ($hidden.val()) { $display.val(formatDateDisplay($hidden.val())); }
            $display.bootstrapMaterialDatePicker({
                weekStart: 0, time: false, format: 'ddd, D MMM YYYY', maxDate: new Date(), clearButton: true
            }).on('change', function(e, date) {
                $hidden.val(date ? formatDateDB(date) : '');

                // If this is the company_reg_date, update the AR/BO (month_no) field
                if (fieldName === 'company_reg_date') {
                    if (date) {
                        // date is a moment object from the datepicker
                        var monthNum = date.month() + 1; // moment months are 0-indexed
                        var month = String(monthNum).padStart(2, '0');
                        $('#month_no').val(month);
                    } else {
                        $('#month_no').val('');
                    }
                }
            });
        });


        // Initialize sd_datepicker_max (max date = today)
        $('.sd_datepicker_all').each(function() {
            var $display = $(this);
            var fieldName = $display.attr('id').replace('_display', '');
            var $hidden = $('#' + fieldName);
            if ($hidden.val()) { $display.val(formatDateDisplay($hidden.val())); }
            $display.bootstrapMaterialDatePicker({
                weekStart: 0, time: false, format: 'ddd, D MMM YYYY', clearButton: true
            }).on('change', function(e, date) {
                $hidden.val(date ? formatDateDB(date) : '');

                // If this is the company_reg_date, update the AR/BO (month_no) field
                if (fieldName === 'company_reg_date') {
                    if (date) {
                        // date is a moment object from the datepicker
                        var monthNum = date.month() + 1; // moment months are 0-indexed
                        var month = String(monthNum).padStart(2, '0');
                        $('#month_no').val(month);
                    } else {
                        $('#month_no').val('');
                    }
                }
            });
        });

        // On page load, set AR/BO from existing company_reg_date if available
        var existingRegDate = $('#company_reg_date').val();
        if (existingRegDate) {
            var d = new Date(existingRegDate);
            if (!isNaN(d.getTime())) {
                var month = String(d.getMonth() + 1).padStart(2, '0');
                $('#month_no').val(month);
            }
        }

    // Also update AR/BO when date picker directly sets a value
    $('#company_reg_date_display').on('change', function() {
        var hiddenVal = $('#company_reg_date').val();
        if (hiddenVal) {
            var d = new Date(hiddenVal);
            if (!isNaN(d.getTime())) {
                var month = String(d.getMonth() + 1).padStart(2, '0');
                $('#month_no').val(month);
                $("#tax_reg_date").val(formatDateDisplay(d))
                $("#tax_reg_date_display").val(formatDateDisplay(d))

            }
        } else {
            $('#month_no').val('');
        }
    });
    } else {
        console.log('Bootstrap Material DatePicker not loaded');
    }
});
</script>


<script>
    // ============================================
    // SD_10_DIGIT - Limit input to digits only (0-9)
    // Usage: sd_10_digit(inputElement, maxDigits)
    // ============================================
    function sd_10_digit(input, maxDigits) {
        maxDigits = maxDigits || 10;
        var $input = $(input);
        var val = $input.val();
        
        // Remove all non-digit characters
        val = val.replace(/[^0-9]/g, '');
        
        // Limit to maxDigits
        if (val.length > maxDigits) {
            val = val.substring(0, maxDigits);
        }
        $input.val(val.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3'));
    }

    function sd_fnb_number_format(input,bank_name=null) {
        var maxDigits = 10;
        var $input = $(input);
        var val = $input.val();
        
        // Remove all non-digit characters
        val = val.replace(/[^0-9]/g, '');
        
        // Limit to maxDigits
        if(bank_name == 3){ // First National Bank - Max Digit 11
            maxDigits = 11;
            if (val.length > maxDigits) {
                val = val.substring(0, maxDigits);
            }
            $input.val(val.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{3})/, '$1 $2 $3 $4 $5'));
        }

        if(bank_name == 6){ // Standard Bank - Max Digit 11
            maxDigits = 11;
            if (val.length > maxDigits) {
                val = val.substring(0, maxDigits);
            }
            $input.val(val.replace(/(\d{2})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4'));
        }

        if(bank_name == 2 ){ // Capitec Bank - Max Digit 10
            if (val.length > maxDigits) {
                val = val.substring(0, maxDigits);
            }
            $input.val(val.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3'));
        }

        if(bank_name == 1 ){ // Absa Bank - Max Digit 10
            if (val.length > maxDigits) {
                val = val.substring(0, maxDigits);
            }
            $input.val(val.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3'));
        }

        if(bank_name == 5 ){ // Nedbank Bank - Max Digit 10
            if (val.length > maxDigits) {
                val = val.substring(0, maxDigits);
            }
            $input.val(val.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3'));
        }
    }

    $("#bank_account_number").on('input',function(){
        let bank_name = $('#bank_name').val();
        sd_fnb_number_format(this,bank_name)
    })

    // 62 05 05 72 546
    // Apply to tax_number field (10 digits)
    $('#tax_number').on('input', function() {
        sd_10_digit(this, 10);
    });

    // $('#paye_number').on('input', function() {
     
    // });

    // Auto-populate SDL and UIF numbers from PAYE number
  $('#paye_number').on('keyup', function() {
        sd_10_digit(this, 10);
        var $input = $(this);
        var $successFeedback = $('#paye_number_feedback_success');
        var $errorFeedback = $('#paye_number_feedback_error');
        var paye = $input.val().replace(/\s/g, '');

        // Hide both tooltips initially
        $successFeedback.removeClass('show');
        $errorFeedback.removeClass('show');
        $input.removeClass('is-invalid is-valid');

        // Format: X 13 078 7882 (letter, space, then digits grouped as 2-3-4)
        var formatWithPrefix = function(val) {
            var prefix = val.charAt(0);
            var digits = val.substring(1);
            var formatted = prefix + ' ';
            for (var i = 0; i < digits.length; i++) {
                if (i === 2 || (i > 2 && (i - 2) % 3 === 0)) {
                    formatted += ' ';
                }
                formatted += digits.charAt(i);
            }
            return formatted;
        };

        // Skip validation if empty
        if (!paye) {
            $('#sdl_number').val('');
            $('#uif_number').val('');
            return;
        }

        // Always populate SDL and UIF on each keyup (progressive)
        var suffix = paye.substring(1);
        if (suffix.length > 0) {
            $('#sdl_number').val(formatWithPrefix('S' + suffix));
            $('#uif_number').val(formatWithPrefix('U' + suffix));
        } else {
            $('#sdl_number').val('');
            $('#uif_number').val('');
        }

        // Validate: must start with 7
        if (paye.charAt(0) !== '7') {
            $errorFeedback.text('PAYE number must start with 7').addClass('show');
            $input.addClass('is-invalid');
            return;
        }

        // Validate: must be exactly 10 digits
        if (paye.length !== 10) {
           if (paye.length > 1) {
                $errorFeedback.text('PAYE number must be exactly 10 digits (currently ' + paye.length + ')').addClass('show');
                $input.addClass('is-invalid');
            }
            return;
        }

        // Valid PAYE number (starts with 7 and exactly 10 digits)
        $successFeedback.text('Valid PAYE number').addClass('show');
        $input.removeClass('is-invalid').addClass('is-valid');

        // Hide success message after 3 seconds
        setTimeout(function() {
            $successFeedback.removeClass('show');
        }, 3000);
    });

    // $('#vat_number').on('input', function() {
    //     sd_10_digit(this, 10);
    // });
    $('#vat_number').on('keyup', function() {
        sd_10_digit(this, 10);
        var $input = $(this);
        var $successFeedback = $('#vat_number_feedback_success');
        var $errorFeedback = $('#vat_number_feedback_error');
        var vat = $input.val().replace(/\s/g, '');

        // Hide both tooltips initially
        $successFeedback.removeClass('show');
        $errorFeedback.removeClass('show');
        $input.removeClass('is-invalid is-valid');

        // Skip validation if empty
        if (!vat) {
            return;
        }

        // Validate: must start with 4
        if (vat.charAt(0) !== '4') {
            $errorFeedback.text('VAT number must start with 4').addClass('show');
            $input.addClass('is-invalid');
            return;
        }

        // Validate: must be exactly 10 digits
        if (vat.length !== 10) {
             if (vat.length > 1) {
                $errorFeedback.text('VAT number must be exactly 10 digits (currently ' + vat.length + ')').addClass('show');
                $input.addClass('is-invalid');
            }
            return;
        }

        // Valid VAT number (starts with 4 and exactly 10 digits)
        $successFeedback.text('Valid VAT number').addClass('show');
        $input.removeClass('is-invalid').addClass('is-valid');

        // Hide success message after 3 seconds
        setTimeout(function() {
            $successFeedback.removeClass('show');
        }, 3000);
    });

    $('#phone_business').on('input', function() {
        sd_10_digit(this, 10);
    });

    $('#direct').on('input', function() {
        sd_10_digit(this, 10);
    });

    $('#phone_mobile').on('input', function() {
        sd_10_digit(this, 10);
    });

    $('#phone_whatsapp').on('input', function() {
        sd_10_digit(this, 10);
    });

    $('#sars_rep_tax_number').on('input', function() {
        sd_10_digit(this, 10);
    });

    $('#website').on('input', function () {
        $(this).val($(this).val().toLowerCase());
    });
    // Bank name change - fetch branch details and account types via AJAX
    $('#bank_name').on('change', function() {
        var bankId = $(this).val();
        if (!bankId) {
            $('#bank_branch_name').val('');
            $('#bank_branch_code').val('');
            $('#bank_swift_code').val('');
            // Reset account type dropdown
            $('#bank_account_type').html('<option value="">Select Type</option>');
            if ($.fn.selectpicker) { $('#bank_account_type').selectpicker('refresh'); }
            return;
        }

        // Fetch bank details
        $.ajax({
            url: `{{ route('ajax.bank.get', ['id' => '__bankId__']) }}`.replace('__bankId__', bankId),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#bank_branch_name').val(data.branch_name || '');
                $('#bank_branch_code').val(data.branch_code || '');
                $('#bank_swift_code').val(data.swift_code || '');
                $('#bank_logo').val(data.bank_logo || '');
                $('#bank_logo_display').attr('src', `{{ asset('') }}${data.bank_logo}`);


                // Bank Account Type
                var $select = $('#bank_account_type');
                var currentVal = $select.val();
                $select.html('<option value="">Select Type</option>');
                $.each(data.account_types, function(i, type) {
                    let tooltip = `
                    <h4> ${type.bank_account_type.toUpperCase()} </h4>
                    <p>${type.tooltip}</p>
                    `;
                    $select.append(
                        $('<option>')
                            .val(type.id)
                            .text(type.bank_account_type)
                            .attr('data-description', tooltip || '')
                    );
                });

            // Restore previous selection if still available
            if (currentVal) { $select.val(currentVal); }
            
            // Refresh selectpicker
            if ($.fn.selectpicker) { 
                $select.selectpicker('refresh'); 
            }
            
            // ════════════════════════════════════════════════
            // RE-ATTACH TOOLTIP LISTENERS AFTER REFRESH
            // ════════════════════════════════════════════════
       

            // Delay tooltip attachment to ensure dropdown is rendered
            setTimeout(function() {
                $select.next('.dropdown-menu').find('li').each(function() {
                    var $li = $(this);
                    var $option = $select.find('option').eq($li.index());
                    var description = $option.data('description');
                    
                    if (description) {
                        $li.off('mouseenter mouseleave');
                        $li.on('mouseenter', function() {
                            var $tooltip = $('<div class="sd_tooltip_teal show tooltip_reset">' + description + '</div>');
                            $li.append($tooltip);
                        });
                        $li.on('mouseleave', function() {
                            $li.find('.sd_tooltip_teal').remove();
                        });
                    }
                });
            }, 20);



            // Account Status

             var $select = $('#account_status');
                var currentVal = $select.val();
                $select.html('<option value="">Select Status</option>');
                $.each(data.account_statuses, function(i, status) {
                    let tooltip = `
                    <h4> ${status.bank_account_status.toUpperCase()} </h4>
                    <p>${status.tooltip}</p>
                    `;
                    $select.append(
                        $('<option>')
                            .val(status.id)
                            .text(status.bank_account_status)
                            .attr('data-description', tooltip || '')
                    );
                });

                // Restore previous selection if still available
                if (currentVal) { $select.val(currentVal); }
                
                // Refresh selectpicker
                if ($.fn.selectpicker) { 
                    $select.selectpicker('refresh'); 
                }
                
                // ════════════════════════════════════════════════
                // RE-ATTACH TOOLTIP LISTENERS AFTER REFRESH
                // ════════════════════════════════════════════════
        

                // Delay tooltip attachment to ensure dropdown is rendered
                setTimeout(function() {
                    $select.next('.dropdown-menu').find('li').each(function() {
                        var $li = $(this);
                        var $option = $select.find('option').eq($li.index());
                        var description = $option.data('description');
                        
                        if (description) {
                            $li.off('mouseenter mouseleave');
                            $li.on('mouseenter', function() {
                                var $tooltip = $('<div class="sd_tooltip_teal show tooltip_reset">' + description + '</div>');
                                $li.append($tooltip);
                            });
                            $li.on('mouseleave', function() {
                                $li.find('.sd_tooltip_teal').remove();
                            });
                        }
                    });
                }, 20);




                // Bank Statement Frequency
                var $select = $('#bank_statement_frequency');
                var currentVal = $select.val();
                $select.html('<option value="">Select Frequency</option>');
                $.each(data.banke_statement_frequencies, function(i, frequency) {
                    let tooltip = `
                    <h4> ${frequency.bank_account_statement_frequency.toUpperCase()} </h4>
                    <p>${frequency.tooltip}</p>
                    `;
                    $select.append(
                        $('<option>')
                            .val(frequency.id)
                            .text(frequency.bank_account_statement_frequency)
                            .attr('data-description', tooltip || '')
                    );
                });

                // Restore previous selection if still available
                if (currentVal) { $select.val(currentVal); }
                
                // Refresh selectpicker
                if ($.fn.selectpicker) { 
                    $select.selectpicker('refresh'); 
                }
                
                // ════════════════════════════════════════════════
                // RE-ATTACH TOOLTIP LISTENERS AFTER REFRESH
                // ════════════════════════════════════════════════
        

                // Delay tooltip attachment to ensure dropdown is rendered
                setTimeout(function() {
                    $select.next('.dropdown-menu').find('li').each(function() {
                        var $li = $(this);
                        var $option = $select.find('option').eq($li.index());
                        var description = $option.data('description');
                        
                        if (description) {
                            $li.off('mouseenter mouseleave');
                            $li.on('mouseenter', function() {
                                var $tooltip = $('<div class="sd_tooltip_teal show tooltip_reset">' + description + '</div>');
                                $li.append($tooltip);
                            });
                            $li.on('mouseleave', function() {
                                $li.find('.sd_tooltip_teal').remove();
                            });
                        }
                    });
                }, 20);


        
            },
            error: function() {
                $('#bank_branch_name').val('');
                $('#bank_branch_code').val('');
                $('#bank_swift_code').val('');

                $('#bank_account_type').html('<option value="">Select Type</option>');
                if ($.fn.selectpicker) { $('#bank_account_type').selectpicker('refresh'); }
            }
        });
    });


    $('#person_id').on('change', function() {
        var personId = $(this).val();
        if (!personId) {
            $('#identity_number').val('');
            $('#identity_type').val('');
            $('#nationality').val('');
            return;
        }

        // Fetch bank details
        $.ajax({
            url: `{{ route('cimspersons.ajax.person.get', ['id' => '__personId__']) }}`.replace('__personId__', personId),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#director_profile_image').val(data.profile_photo || '');
                $('#director_profile_image_display').attr('src', `${data.profile_photo}`);
                $('#identity_number').val(data.identity_number || '');
                $('#identity_type').val(data.identity_type || '');
                $('#nationality').val(data.nationality || '');
            },
            error: function() {
              console.log("There is error");
            }
        });
    });


    // Address change - fetch All Details details via AJAX
    $('#address').on('change', function() {
        var addressId = $(this).val();
        if (!addressId) {
            $('#unit_number').val('');
            $('#complex_name').val('');
            $('#street_number').val('');
            $('#street_name').val('');
            $('#suburb').val('');
            $('#city').val('');
            $('#postal_code').val('');
            $('#province').val('');
            $('#country').val('');
            $('#municipality').val('');
            $('#ward').val('');
            $('#latitude').val('');
            $('#longitude').val('');
            $('#map_url').val('');
            return;
        }

        // Fetch bank details
        $.ajax({
            url: `{{ route('ajax.address.get', ['id' => '__addressId__']) }}`.replace('__addressId__', addressId),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                
                $('#unit_number').val(data.address.unit_number || '');
                $('#complex_name').val(data.address.complex_name || '');
                $('#street_number').val(data.address.street_number || '');
                $('#street_name').val(data.address.street_name || '');
                $('#suburb').val(data.address.suburb || '');
                $('#city').val(data.address.city || '');
                $('#postal_code').val(data.address.postal_code || '');
                $('#province').val(data.address.province || '');
                $('#country').val(data.address.country || '');
                $('#municipality').val(data.address.municipality || '');
                $('#ward').val(data.address.ward || '');
                $('#latitude').val(data.address.latitude || '');
                $('#longitude').val(data.address.longitude || '');
                $('#map_url').val(data.address.map_url || '');
            },
            error: function() {
                $('#unit_number').val('');
                $('#complex_name').val('');
                $('#street_number').val('');
                $('#street_name').val('');
                $('#suburb').val('');
                $('#city').val('');
                $('#postal_code').val('');
                $('#province').val('');
                $('#country').val('');
                $('#municipality').val('');
                $('#ward').val('');
                $('#latitude').val('');
                $('#longitude').val('');
                $('#map_url').val('');
            }
        });
    });

    $('#number_of_shares').on('input', function() {
        $('#director_share_display').text(this.value);
    });

    $('#number_of_directors').on('input', function() {
        $('#director_display').text(this.value);
    });
</script>


@endpush



@push('scripts')
<script>
    // ══════════════════════════════════════
    // GLOBAL VARIABLES - Accessible across all scripts
    // ══════════════════════════════════════
    let savedBanks = [];
    let selectedBankIds = [];
    let bankFiles = {};
    let deletedBankIds = [];

    let savedAddresses = [];
    let addressFiles = {};
    let deletedAddressIds = [];

    let savedDirectors = [];
</script>

@include('cims_pm_pro::clientmaster.bank_details_js')
@include('cims_pm_pro::clientmaster.address_details_js')
@include('cims_pm_pro::clientmaster.director_js')

    <script>
document.addEventListener('DOMContentLoaded', function() {

        document.querySelector('#client_form').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') {
                e.preventDefault();
            }
        });
         // ══════════════════════════════════════
        // FORM SUBMISSION — inject NEW banks + existing bank selections + deleted bank IDs
        // ══════════════════════════════════════
        $('#client_form').on('submit', function(e) {
            

            // ══════════════════════════════════════
            // VALIDATE PAYE number on submit
            // ══════════════════════════════════════
            var paye = $('#paye_number').val().replace(/\s/g, '');
            if (paye) {
                if (paye.charAt(0) !== '7') {
                    e.preventDefault();
                    $('#paye_number_feedback_error').text('PAYE number must start with 7').addClass('show');
                    $('#paye_number').addClass('is-invalid').focus();
                    return false;
                }
                if (paye.length !== 10) {
                    e.preventDefault();
                    $('#paye_number_feedback_error').text('PAYE number must be exactly 10 digits (currently ' + paye.length + ')').addClass('show');
                    $('#paye_number').addClass('is-invalid').focus();
                    return false;
                }
            }


            var vat = $('#vat_number').val().replace(/\s/g, '');
            if (vat) {
                if (vat.charAt(0) !== '4') {
                    e.preventDefault();
                    $('#vat_number_feedback_error').text('VAT number must start with 4').addClass('show');
                    $('#vat_number').addClass('is-invalid').focus();
                    return false;
                }
                if (vat.length !== 10) {
                    
                    e.preventDefault();
                    $('#vat_number_feedback_error').text('VAT number must be exactly 10 digits (currently ' + vat.length + ')').addClass('show');
                    $('#vat_number').addClass('is-invalid').focus();
                    return false;
                }
            }


            // Remove the original bank form field names so they don't conflict
            $('#bank-form').find('input[name], select[name]').each(function() {
                $(this).removeAttr('name');
            });

            // Remove any previously injected bank hidden inputs
            $(this).find('.bank-hidden-input').remove();

            const $form = $(this);

            // Process NEW banks (without db_id)
            const newBanks = savedBanks.filter(b => !b.is_existing && !b.is_deleted);
            newBanks.forEach((bank, index) => {
                const fields = [
                    'bank_id', 'bank_name', 'bank_account_holder', 'bank_account_number',
                    'bank_account_type_id', 'bank_account_type_name', 'bank_account_status_id','bank_account_status_name',
                    'bank_branch_name', 'bank_branch_code', 'bank_swift_code',
                    'bank_account_date_opened','bank_statement_frequency_id','bank_statement_frequency_name','bank_statement_cut_off_date'
                ];

                fields.forEach(field => {
                    $form.append(
                        $('<input>').attr({
                            type: 'hidden',
                            name: `banks[${index}][${field}]`,
                            value: bank[field] || ''
                        }).addClass('bank-hidden-input')
                    );
                });

                // Add is_checked based on selectedBankIds
                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `banks[${index}][is_default]`,
                        value: bank.is_default ? 1 : 0 
                    }).addClass('bank-hidden-input')
                );

                // Handle file: create a file input and transfer the File object
                if (bankFiles[bank.id]) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(bankFiles[bank.id]);
                        const fileInput = document.createElement('input');
                        fileInput.type = 'file';
                        fileInput.name = `banks[${index}][confirmation_file]`;
                        fileInput.files = dt.files;
                        fileInput.className = 'bank-hidden-input';
                        fileInput.style.display = 'none';
                        $form.append(fileInput);
                    } catch (err) {
                        console.warn('Could not attach bank file via DataTransfer:', err);
                    }
                }
            });

            // Process EXISTING banks — update their is_checked status
            const existingBanks = savedBanks.filter(b => b.is_existing && !b.is_deleted);
            existingBanks.forEach((bank, index) => {
                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `existing_banks[${index}][id]`,
                        value: bank.db_id
                    }).addClass('bank-hidden-input')
                );

                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `existing_banks[${index}][is_default]`,
                        value: bank.is_default ? 1 : 0
                    }).addClass('bank-hidden-input')
                );
            });

            // Inject deleted bank IDs
            deletedBankIds.forEach((bankId, index) => {
                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `deleted_bank_ids[${index}]`,
                        value: bankId
                    }).addClass('bank-hidden-input')
                );
            });


            // Addresses
            // Remove the original address form field names so they don't conflict
            $('#address-form').find('input[name], select[name]').each(function() {
                $(this).removeAttr('name');
            });

            // Remove any previously injected address hidden inputs
            $(this).find('.address-hidden-input').remove();

            const newAddresses = savedAddresses.filter(a => !a.is_existing && !a.is_deleted);
            newAddresses.forEach((address, index) => {
                const fields = [
                    'address_id', 'address_name', 'address_type_id','address_type_name'
                ];

                fields.forEach(field => {
                    $form.append(
                        $('<input>').attr({
                            type: 'hidden',
                            name: `addresses[${index}][${field}]`,
                            value: address[field] || ''
                        }).addClass('address-hidden-input')
                    );
                });

                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `addresses[${index}][is_default]`,
                        value: address.is_default ? 1 : 0  // Convert boolean to integer
                    }).addClass('address-hidden-input')
                );

            });

            // Process EXISTING addresses — update their default flag
            const existingAddresses = savedAddresses.filter(a => a.is_existing && !a.is_deleted);
            existingAddresses.forEach((address, index) => {
                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `existing_addresses[${index}][id]`,
                        value: address.db_id
                    }).addClass('address-hidden-input')
                );

                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `existing_addresses[${index}][is_default]`,
                        value: address.is_default ? 1 : 0
                    }).addClass('address-hidden-input')
                );
            });

            // Inject deleted address IDs
            deletedAddressIds.forEach((addressId, index) => {
                $form.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: `deleted_address_ids[${index}]`,
                        value: addressId
                    }).addClass('address-hidden-input')
                );
            });
            // Let the form submit naturally



            // Directors

             $('#director-form').find('input[name], select[name]').each(function() {
                $(this).removeAttr('name');
            });

            // Remove any previously injected address hidden inputs
            $(this).find('.director-hidden-input').remove();

            const newDirectors = savedDirectors.filter(a => !a.is_existing && !a.is_deleted);
            newDirectors.forEach((director, index) => {
                const fields = [
                    'director_type_id','director_type_name', 'person_id', 'date_engaged','date_resigned','director_status_id','director_status_name','number_of_director_shares'
                ];

                fields.forEach(field => {
                    $form.append(
                        $('<input>').attr({
                            type: 'hidden',
                            name: `directors[${index}][${field}]`,
                            value: director[field] || ''
                        }).addClass('director-hidden-input')
                    );
                });

            });

            // Process EXISTING addresses — update their default flag
            // const existingDirectors = savedDirectors.filter(a => a.is_existing && !a.is_deleted);
            // existingDirectors.forEach((director, index) => {
            //     $form.append(
            //         $('<input>').attr({
            //             type: 'hidden',
            //             name: `existing_directors[${index}][id]`,
            //             value: director.db_id
            //         }).addClass('director-hidden-input')
            //     );
            // });

            // signature_data is already inside the form via the signature pad component hidden input

            return true;
        });
                    
});
    </script>

@endpush