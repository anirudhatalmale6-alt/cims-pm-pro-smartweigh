@extends('cimsdocmanager::layouts.default')

@section('title', isset($document) ? 'Edit Document' : 'Upload Document')

@push('styles')
<link href="/public/smartdash/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
<link href="/public/smartdash/css/smartdash-forms.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* Fix double border on sd_drop_class dropdowns - keep OUTSIDE border (bootstrap-select button) */
.bootstrap-select.sd_drop_class > .dropdown-toggle {
    border: 2px solid #17a2b8 !important;
    border-radius: 8px !important;
    min-height: 48px !important;
    background-color: #fff !important;
}
/* Remove ALL inner borders from Bootstrap-Select components */
.bootstrap-select.sd_drop_class {
    border: none !important;
}
.bootstrap-select.sd_drop_class .dropdown-toggle .filter-option,
.bootstrap-select.sd_drop_class .dropdown-toggle .filter-option-inner,
.bootstrap-select.sd_drop_class .dropdown-toggle .filter-option-inner-inner,
.bootstrap-select.sd_drop_class .filter-option,
.bootstrap-select.sd_drop_class .filter-option-inner,
.bootstrap-select.sd_drop_class .filter-option-inner-inner {
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    background: transparent !important;
}
/* Hide the inner select element completely */
select.sd_drop_class,
select.sd_drop_class.form-control,
.smartdash-form-card select.sd_drop_class,
.smartdash-form-card select.sd_drop_class.form-control,
.card select.sd_drop_class,
.card select.sd_drop_class.form-control {
    position: absolute !important;
    opacity: 0 !important;
    visibility: hidden !important;
    height: 0 !important;
    width: 0 !important;
    border: none !important;
    border-width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    display: none !important;
}
</style>
@endpush

@php
    // Helper function to safely format dates
    function formatDateValue($value) {
        if (empty($value)) return '';
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }
        // Already a string, return as-is if it looks like a date
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return substr($value, 0, 10);
        }
        return $value;
    }
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row page-titles">
        <div class="d-flex align-items-center justify-content-between">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="fs-2" style="color:#000" href="javascript:void(0)">CIMS</a></li>
                <li class="breadcrumb-item"><a class="fs-2" style="color:#17A2B8" href="/cims/docmanager">Document Manager</a></li>
                <li class="breadcrumb-item active"><a class="fs-2" style="color:#009688" href="javascript:void(0)">{{ isset($document) ? 'Edit' : 'Upload' }}</a></li>
            </ol>
            <a href="/cims/docmanager" class="btn btn-outline-secondary btn-lg">
                <i class="fa fa-list"></i> All Documents
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card smartdash-form-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fa fa-file-alt"></i>
                        {{ isset($document) ? 'Edit Document' : 'Upload New Document' }}
                    </h4>
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

                    <form action="{{ isset($document) ? '/cims/docmanager/'.$document->id : '/cims/docmanager' }}" method="POST" enctype="multipart/form-data" id="documentForm">
                        @csrf
                        @if(isset($document))
                            @method('PUT')
                        @endif

                        <!-- Client Selection Section -->
                        <div class="form-section-title">
                            <i class="fa fa-building"></i> Client Selection
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select name="client_id" id="client_id" class="form-control sd_drop_class" data-live-search="true" title="Search and select a client...">
                                        <option value="">Please select</option>
                                        @foreach($clients as $client)
                                        <option value="{{ $client->id }}"
                                                data-name="{{ $client->name }}"
                                                data-code="{{ $client->code }}"
                                                data-email="{{ $client->email }}"
                                                data-reg="{{ $client->reg_number }}"
                                                {{ old('client_id', $document->client_id ?? $selectedClientId ?? '') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->code }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="client_code" class="form-label">Client Code</label>
                                    <input type="text" name="client_code" id="client_code" class="form-control"
                                           value="{{ old('client_code', $document->client_code ?? '') }}" readonly>
                                    <input type="hidden" name="client_name" id="client_name" value="{{ old('client_name', $document->client_name ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="registration_number" class="form-label">Registration Number</label>
                                    <input type="text" name="registration_number" id="registration_number" class="form-control"
                                           value="{{ old('registration_number', $document->registration_number ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="client_email" class="form-label">Email</label>
                                    <input type="email" name="client_email" id="client_email" class="form-control"
                                           value="{{ old('client_email', $document->client_email ?? '') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Document Classification Section -->
                        <div class="form-section-title">
                            <i class="fa fa-tags"></i> Document Classification
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control sd_drop_class @error('category_id') is-invalid @enderror" data-live-search="true" title="Select category" required>
                                        <option value="">Please select</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                                {{ old('category_id', $document->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type_id" class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select name="type_id" id="type_id" class="form-control sd_drop_class @error('type_id') is-invalid @enderror" data-live-search="true" title="Select document type" required>
                                        <option value="">Please select</option>
                                        @foreach($types as $type)
                                        <option value="{{ $type->id }}"
                                                data-doc-ref="{{ $type->doc_ref ?? '' }}"
                                                data-doc-group="{{ $type->doc_group ?? '' }}"
                                                data-has-expiry="{{ $type->has_expiry ? 'YES' : 'NO' }}"
                                                data-days-to-expire="{{ $type->days_to_expire ?? '' }}"
                                                data-lead-time="{{ $type->lead_time_days ?? 30 }}"
                                                data-category="{{ $type->category_id }}"
                                                {{ old('type_id', $document->type_id ?? '') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="period_id" class="form-label">Select Period</label>
                                    <select name="period_id" id="period_id" class="form-control sd_drop_class" data-live-search="true" title="Select period">
                                        <option value="">Please select</option>
                                        @foreach($periods as $period)
                                        <option value="{{ $period->id }}"
                                                data-combo="{{ $period->period_combo }}"
                                                {{ old('period_id', $document->period_id ?? '') == $period->id ? 'selected' : '' }}>
                                            {{ $period->period_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Document Details Section -->
                        <div class="form-section-title">
                            <i class="fa fa-calendar"></i> Document Details
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="issue_date" class="form-label">Issue Date</label>
                                    <input type="date" name="issue_date" id="issue_date" class="form-control"
                                           value="{{ old('issue_date', formatDateValue($document->issue_date ?? now())) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" id="expiry_date" class="form-control"
                                           value="{{ old('expiry_date', formatDateValue($document->expiry_date ?? '')) }}">
                                    <input type="hidden" name="has_expiry" id="has_expiry" value="{{ old('has_expiry', $document->has_expiry ?? 'NO') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="uploaded_by" class="form-label">Uploaded By</label>
                                    <input type="text" id="uploaded_by" class="form-control" value="{{ auth()->user()->name ?? 'System' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control sd_drop_class" title="Select status">
                                        <option value="Current" {{ old('status', $document->status ?? 'Current') == 'Current' ? 'selected' : '' }}>Current</option>
                                        <option value="Archived" {{ old('status', $document->status ?? '') == 'Archived' ? 'selected' : '' }}>Archived</option>
                                        <option value="Superseded" {{ old('status', $document->status ?? '') == 'Superseded' ? 'selected' : '' }}>Superseded</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Title & Description Section -->
                        <div class="form-section-title">
                            <i class="fa fa-edit"></i> Title & Description
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                           placeholder="Enter document title" value="{{ old('title', $document->title ?? '') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Description (auto-generated)">{{ old('description', $document->description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload Section -->
                        <div class="form-section-title">
                            <i class="fa fa-cloud-upload-alt"></i> File Upload (Optional)
                        </div>
                        @if(isset($document) && $document->file_stored_name)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa {{ $document->file_icon ?? 'fa-file' }} me-2"></i>
                                    <strong>Current File:</strong> {{ $document->file_original_name }}
                                    <a href="/cims/docmanager/{{ $document->id }}/download" class="btn btn-sm btn-outline-primary ms-3">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">{{ isset($document) ? 'Replace File (Optional)' : 'Upload File (Optional)' }}</label>
                                    <div class="file-preview" id="filePreview" style="border: 2px dashed #dee2e6; border-radius: 10px; padding: 30px 20px; text-align: center; background: #f8f9fa; cursor: pointer;">
                                        <i class="fa fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                        <p class="mb-1"><strong>Drop File Here</strong></p>
                                        <small class="text-muted">Click to Upload (Optional)</small>
                                    </div>
                                    <input type="file" name="file" id="fileInput" class="d-none @error('file') is-invalid @enderror"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.mp3,.mp4,.m4a,.html,.htm,.txt,.zip,.rar,.csv,.pptx">
                                    @error('file')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="file_name_display" class="form-label">File Name</label>
                                    <input type="text" name="file_name_display" id="file_name_display" class="form-control"
                                           value="{{ old('file_name_display', $document->file_original_name ?? '') }}"
                                           placeholder="No file selected" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="form-section-title">
                            <i class="fa fa-sticky-note"></i> Notes
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notesField" class="form-control" rows="3" maxlength="200" placeholder="Additional notes...">{{ old('notes', $document->notes ?? '') }}</textarea>
                                    <small class="text-muted"><span id="charCount">200</span> characters remaining</small>
                                </div>
                            </div>
                        </div>

                        <!-- Expiry Settings Section -->
                        <div class="form-section-title">
                            <i class="fa fa-clock"></i> Expiry Settings
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="expire_reminder" class="form-label">Expire</label>
                                    <select name="expire_reminder" id="expire_reminder" class="form-control sd_drop_class" title="Select">
                                        <option value="YES" {{ old('expire_reminder', $document->has_expiry ?? '') == 'YES' ? 'selected' : '' }}>Yes</option>
                                        <option value="NO" {{ old('expire_reminder', $document->has_expiry ?? 'NO') == 'NO' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="days_to_expire" class="form-label">in Days</label>
                                    <input type="number" name="days_to_expire" id="days_to_expire" class="form-control"
                                           value="{{ old('days_to_expire', $document->days_to_expire ?? '') }}"
                                           placeholder="Days">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="lead_time_days" class="form-label">Lead</label>
                                    <input type="number" name="lead_time_days" id="lead_time_days" class="form-control"
                                           value="{{ old('lead_time_days', $document->lead_time_days ?? 30) }}"
                                           placeholder="Lead days">
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="financial_year" value="{{ old('financial_year', $document->financial_year ?? date('Y')) }}">

                        <!-- Submit Buttons -->
                        <div class="mb-3 d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> {{ isset($document) ? 'Update Document' : 'Save Document' }}
                            </button>
                            <a href="/cims/docmanager" class="btn btn-outline-secondary btn-lg">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="/public/smartdash/vendor/sweetalert2/sweetalert2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Bootstrap-Select for all dropdowns with sd_drop_class
    if ($.fn.selectpicker) {
        $('.sd_drop_class').selectpicker({
            liveSearch: true,
            liveSearchPlaceholder: 'Search...',
            size: 10
        });
    }

    // =====================================================
    // CLIENT SELECTION HANDLER
    // =====================================================
    $('#client_id').on('changed.bs.select change', function() {
        var selected = $(this).find('option:selected');
        $('#client_name').val(selected.data('name') || '');
        $('#client_code').val(selected.data('code') || '');
        $('#client_email').val(selected.data('email') || '');
        $('#registration_number').val(selected.data('reg') || '');

        // Reset category and type when client changes
        $('#category_id').val('');
        $('#type_id').val('').prop('disabled', true);
        if ($.fn.selectpicker) {
            $('#category_id').selectpicker('refresh');
            $('#type_id').selectpicker('refresh');
        }

        // Update description
        updateDescription();
    });

    // =====================================================
    // CATEGORY / DOCUMENT TYPE DEPENDENCY
    // =====================================================

    // Initially disable Document Type if no category selected
    var initialCategoryId = $('#category_id').val();
    if (!initialCategoryId) {
        $('#type_id').prop('disabled', true);
        if ($.fn.selectpicker) {
            $('#type_id').selectpicker('refresh');
        }
    } else {
        // If category is pre-selected (edit mode), filter types
        $('#type_id option').each(function() {
            var typeCategory = $(this).data('category');
            if (typeCategory && typeCategory != initialCategoryId && $(this).val() !== '') {
                $(this).prop('disabled', true).hide();
            }
        });
        if ($.fn.selectpicker) {
            $('#type_id').selectpicker('refresh');
        }
    }

    // Category change - filter document types
    $('#category_id').on('changed.bs.select change', function() {
        var categoryId = $(this).val();

        if (!categoryId) {
            // No category selected - disable document type
            $('#type_id').prop('disabled', true).val('');
            $('#type_id option').each(function() {
                if ($(this).val() !== '') {
                    $(this).prop('disabled', true).hide();
                }
            });
        } else {
            // Category selected - enable and filter document types
            $('#type_id').prop('disabled', false);
            $('#type_id option').each(function() {
                var typeCategory = $(this).data('category');
                if ($(this).val() === '') {
                    $(this).prop('disabled', false).show();
                } else if (typeCategory == categoryId) {
                    $(this).prop('disabled', false).show();
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
        }

        // Reset document type selection
        $('#type_id').val('');
        if ($.fn.selectpicker) {
            $('#type_id').selectpicker('refresh');
        }

        updateDescription();
    });

    // =====================================================
    // DOCUMENT TYPE CHANGE - SET EXPIRY FIELDS
    // =====================================================
    $('#type_id').on('changed.bs.select change', function() {
        var selected = $(this).find('option:selected');
        var hasExpiry = selected.data('has-expiry');
        var daysToExpire = selected.data('days-to-expire') || '';
        var docRef = selected.data('doc-ref') || '';
        var docGroup = selected.data('doc-group') || '';

        // Set expiry fields based on document type
        if (hasExpiry === 'YES' || hasExpiry === 1 || hasExpiry === '1') {
            $('#has_expiry').val('YES');
            $('#expire_reminder').val('YES');
            if ($.fn.selectpicker) {
                $('#expire_reminder').selectpicker('refresh');
            }

            // Calculate expiry date if issue date is set
            var issueDate = $('#issue_date').val();
            if (issueDate && daysToExpire) {
                var leadDays = parseInt($('#lead_time_days').val()) || 0;
                var expiryDays = parseInt(daysToExpire) - leadDays;
                if (expiryDays > 0) {
                    var expDate = new Date(issueDate);
                    expDate.setDate(expDate.getDate() + expiryDays);
                    $('#expiry_date').val(expDate.toISOString().split('T')[0]);
                }
            }
            $('#days_to_expire').val(daysToExpire);
        } else {
            $('#has_expiry').val('NO');
            $('#expire_reminder').val('NO');
            $('#expiry_date').val('');
            $('#days_to_expire').val('');
            if ($.fn.selectpicker) {
                $('#expire_reminder').selectpicker('refresh');
            }
        }

        updateDescription();
    });

    // =====================================================
    // PERIOD CHANGE
    // =====================================================
    $('#period_id').on('changed.bs.select change', function() {
        var selected = $(this).find('option:selected');
        var periodCombo = selected.data('combo') || '';
        updateDescription();
    });

    // =====================================================
    // ISSUE DATE CHANGE - CALCULATE EXPIRY DATE
    // =====================================================
    $('#issue_date').on('change', function() {
        var issueDate = $(this).val();
        var daysToExpire = parseInt($('#days_to_expire').val()) || 0;
        var leadDays = parseInt($('#lead_time_days').val()) || 0;
        var hasExpiry = $('#has_expiry').val();

        if (issueDate && daysToExpire > 0 && hasExpiry === 'YES') {
            var expiryDays = daysToExpire - leadDays;
            if (expiryDays > 0) {
                var expDate = new Date(issueDate);
                expDate.setDate(expDate.getDate() + expiryDays);
                $('#expiry_date').val(expDate.toISOString().split('T')[0]);
            }
        }

        updateDescription();
    });

    // =====================================================
    // EXPIRY DATE CHANGE
    // =====================================================
    $('input[name="expiry_date"]').on('change', function() {
        if ($(this).val()) {
            $('#has_expiry').val('YES');
        } else {
            $('#has_expiry').val('NO');
        }
    });

    // =====================================================
    // UPDATE DESCRIPTION AUTO-FILL
    // =====================================================
    function updateDescription() {
        var clientName = $('#client_id option:selected').text() || '';
        var clientCode = $('#client_code').val() || '';
        var regNumber = $('#registration_number').val() || '';
        var category = $('#category_id option:selected').text() || '';
        var docType = $('#type_id option:selected').text() || '';
        var period = $('#period_id option:selected').text() || '';
        var uploadedBy = $('#uploaded_by').val() || 'System';
        var issueDate = $('#issue_date').val() || '';

        if (clientName && clientName !== 'Please select' && docType && docType !== 'Please select') {
            var desc = clientName;
            if (clientCode) desc += ' [' + clientCode + ']';
            desc += ' - ' + docType;
            if (period && period !== 'Please select') desc += ' for period [' + period + ']';
            if (regNumber) desc += ' - Reg: ' + regNumber;
            desc += ' - Uploaded by ' + uploadedBy;
            if (issueDate) desc += ' on ' + issueDate;

            $('#description').val(desc);
        }
    }

    // File input preview
    var fileInput = document.getElementById('fileInput');
    var filePreview = document.getElementById('filePreview');

    filePreview.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            var file = this.files[0];
            filePreview.style.borderColor = '#17A2B8';
            filePreview.style.background = '#e8f7f9';
            filePreview.innerHTML = '<i class="fa fa-check-circle fa-2x text-success mb-2"></i><p class="mb-1"><strong>' + truncateFileName(file.name, 30) + '</strong></p><small class="text-muted">' + formatFileSize(file.size) + '</small>';
            document.getElementById('file_name_display').value = file.name;
        }
    });

    // Drag and drop
    filePreview.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#17A2B8';
        this.style.background = '#e8f7f9';
    });

    filePreview.addEventListener('dragleave', function(e) {
        e.preventDefault();
        if (!fileInput.files || !fileInput.files[0]) {
            this.style.borderColor = '#dee2e6';
            this.style.background = '#f8f9fa';
        }
    });

    filePreview.addEventListener('drop', function(e) {
        e.preventDefault();
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            var event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });

    function formatFileSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' bytes';
    }

    function truncateFileName(name, length) {
        if (name.length <= length) return name;
        var ext = name.split('.').pop();
        var base = name.substring(0, length - ext.length - 4);
        return base + '...' + ext;
    }

    // Trigger client selection if pre-selected
    if ($('#client_id').val()) {
        $('#client_id').trigger('change');
    }

    // Notes character counter
    var notesField = document.getElementById('notesField');
    var charCount = document.getElementById('charCount');
    if (notesField && charCount) {
        function updateCharCount() {
            var remaining = 200 - notesField.value.length;
            charCount.textContent = remaining;
        }
        notesField.addEventListener('input', updateCharCount);
        updateCharCount();
    }
});

@if(session('error'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        html: '<div style="font-size: 16px;">{!! addslashes(session('error')) !!}</div>',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545',
        allowOutsideClick: false,
        allowEscapeKey: false
    });
});
@endif

@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#28a745',
        timer: 3000,
        timerProgressBar: true
    });
});
@endif
</script>
@endpush
