@extends('layouts.default')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Email</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">My Signature</a></li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-xxl-4">
                                @include('cims_email::emails.partials.sidebar', ['activePage' => 'signature'])
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h4 class="card-title mb-0"><i class="fas fa-signature me-2 text-primary"></i>My Email Signature</h4>
                                    </div>

                                    @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    @endif

                                    <form method="POST" action="{{ route('cimsemail.signature.save') }}">
                                        @csrf
                                        {{-- Personal Details --}}
                                        <div class="filter cm-content-box box-primary">
                                            <div class="content-title SlideToolHeader">
                                                <div class="cpa">
                                                    <i class="fas fa-user me-2"></i>Personal Details
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                                </div>
                                            </div>
                                            <div class="cm-content-body form excerpt">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="full_name" class="form-control" placeholder="e.g. John Smith" value="{{ $signature->full_name ?? '' }}" required id="sigName">
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">Designation / Title <span class="text-danger">*</span></label>
                                                            <input type="text" name="designation" class="form-control" placeholder="e.g. Tax Consultant" value="{{ $signature->designation ?? '' }}" required id="sigTitle">
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">Phone</label>
                                                            <input type="text" name="phone" class="form-control" placeholder="e.g. +27 11 123 4567" value="{{ $signature->phone ?? '' }}" id="sigPhone">
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">Mobile</label>
                                                            <input type="text" name="mobile" class="form-control" placeholder="e.g. +27 82 123 4567" value="{{ $signature->mobile ?? '' }}" id="sigMobile">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Company Details --}}
                                        <div class="filter cm-content-box box-primary mt-4">
                                            <div class="content-title SlideToolHeader">
                                                <div class="cpa">
                                                    <i class="fas fa-building me-2"></i>Company Details
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                                </div>
                                            </div>
                                            <div class="cm-content-body form excerpt">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">Company Name</label>
                                                            <input type="text" name="company_name" class="form-control" placeholder="e.g. Accounting Taxation and Payroll (Pty) Ltd" value="{{ $signature->company_name ?? '' }}" id="sigCompany">
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">Company Website</label>
                                                            <input type="text" name="company_website" class="form-control" placeholder="e.g. www.company.co.za" value="{{ $signature->company_website ?? '' }}" id="sigWebsite">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Custom Signature HTML (Advanced) --}}
                                        <div class="filter cm-content-box box-primary mt-4">
                                            <div class="content-title SlideToolHeader">
                                                <div class="cpa">
                                                    <i class="fas fa-code me-2"></i>Custom Signature (Advanced - Optional)
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                                </div>
                                            </div>
                                            <div class="cm-content-body form excerpt" style="display:none;">
                                                <div class="card-body">
                                                    <p class="text-muted mb-3" style="font-size:12px;">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Leave this empty to use the auto-generated signature from the fields above.
                                                        Only use this if you want a fully custom HTML signature.
                                                    </p>
                                                    <textarea name="signature_html" id="sigCustomHtml" class="form-control" rows="6" placeholder="Paste custom HTML signature here (optional)...">{{ $signature->signature_html ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Live Preview --}}
                                        <div class="filter cm-content-box box-primary mt-4">
                                            <div class="content-title SlideToolHeader">
                                                <div class="cpa">
                                                    <i class="fas fa-eye me-2"></i>Signature Preview
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                                </div>
                                            </div>
                                            <div class="cm-content-body form excerpt">
                                                <div class="card-body">
                                                    <div id="sigPreview" style="padding:20px;background:#fff;border:1px solid #eee;border-radius:6px;">
                                                        {{-- Preview will be rendered by JS --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="mt-4 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Signature
                                            </button>
                                            <button type="button" class="btn btn-info light" onclick="updatePreview()">
                                                <i class="fas fa-eye me-2"></i>Refresh Preview
                                            </button>
                                        </div>
                                    </form>
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
function buildSignatureHtml() {
    var name = document.getElementById('sigName').value || '';
    var title = document.getElementById('sigTitle').value || '';
    var phone = document.getElementById('sigPhone').value || '';
    var mobile = document.getElementById('sigMobile').value || '';
    var company = document.getElementById('sigCompany').value || '';
    var website = document.getElementById('sigWebsite').value || '';

    var html = '<table cellpadding="0" cellspacing="0" style="font-family:Arial,sans-serif;font-size:13px;color:#333;border-collapse:collapse;">';
    html += '<tr><td style="padding-bottom:8px;border-bottom:2px solid var(--primary, #6853E8);">';
    html += '<strong style="font-size:15px;color:#1a1a2e;">' + name + '</strong>';
    if (title) html += '<br><span style="font-size:12px;color:#666;">' + title + '</span>';
    html += '</td></tr>';

    html += '<tr><td style="padding-top:8px;">';
    var contactLines = [];
    if (phone) contactLines.push('<i class="fas fa-phone" style="color:var(--primary,#6853E8);width:16px;font-size:11px;"></i> ' + phone);
    if (mobile) contactLines.push('<i class="fas fa-mobile-alt" style="color:var(--primary,#6853E8);width:16px;font-size:11px;"></i> ' + mobile);
    if (contactLines.length > 0) {
        html += '<span style="font-size:12px;color:#555;">' + contactLines.join(' &nbsp;&nbsp;|&nbsp;&nbsp; ') + '</span><br>';
    }

    if (company) {
        html += '<strong style="font-size:12px;color:#1a1a2e;">' + company + '</strong>';
        if (website) html += ' &nbsp;|&nbsp; <a href="https://' + website.replace(/^https?:\/\//, '') + '" style="font-size:12px;color:var(--primary,#6853E8);text-decoration:none;">' + website + '</a>';
        html += '<br>';
    }
    html += '</td></tr>';
    html += '</table>';
    return html;
}

function updatePreview() {
    var customHtml = document.getElementById('sigCustomHtml').value.trim();
    var previewDiv = document.getElementById('sigPreview');

    if (customHtml) {
        previewDiv.innerHTML = customHtml;
    } else {
        previewDiv.innerHTML = buildSignatureHtml();
    }
}

// Live preview on field changes
document.querySelectorAll('#sigName, #sigTitle, #sigPhone, #sigMobile, #sigCompany, #sigWebsite').forEach(function(el) {
    el.addEventListener('input', function() {
        if (!document.getElementById('sigCustomHtml').value.trim()) {
            updatePreview();
        }
    });
});

document.getElementById('sigCustomHtml').addEventListener('input', function() {
    updatePreview();
});

// Initial preview
updatePreview();

// Fillow SlideToolHeader toggle
jQuery('.SlideToolHeader').on('click', function() {
    var el = jQuery(this).hasClass('expand');
    if (el) {
        jQuery(this).removeClass('expand').addClass('collapse');
        jQuery(this).parents('.cm-content-box').find('.cm-content-body').slideUp(300);
    } else {
        jQuery(this).removeClass('collapse').addClass('expand');
        jQuery(this).parents('.cm-content-box').find('.cm-content-body').slideDown(300);
    }
});
</script>
@endpush
