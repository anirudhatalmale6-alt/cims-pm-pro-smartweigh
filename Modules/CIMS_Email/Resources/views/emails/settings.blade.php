@extends('layouts.default')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Email</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">SMTP Settings</a></li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-xxl-4">
                                @include('cims_email::emails.partials.sidebar', ['activePage' => 'settings'])
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h4 class="card-title mb-0"><i class="fas fa-cog me-2 text-primary"></i>SMTP Settings</h4>
                                    </div>

                                    @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    @endif

                                    @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    @endif

                                    @if(session('test_result'))
                                    <div class="alert alert-{{ session('test_result')['success'] ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                                        <i class="fas fa-{{ session('test_result')['success'] ? 'check-circle' : 'times-circle' }} me-2"></i>
                                        {{ session('test_result')['message'] }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    @endif

                                    <form method="POST" action="{{ route('cimsemail.settings.save') }}">
                                        @csrf
                                        {{-- SMTP Server Settings --}}
                                        <div class="filter cm-content-box box-primary">
                                            <div class="content-title SlideToolHeader">
                                                <div class="cpa">
                                                    <i class="fas fa-server me-2"></i>SMTP Server Configuration
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                                </div>
                                            </div>
                                            <div class="cm-content-body form excerpt">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                                            <input type="text" name="smtp_host" class="form-control" placeholder="e.g. smtp.gmail.com" value="{{ $settings['smtp_host'] ?? '' }}" required>
                                                            <div class="form-text">Mail server hostname (e.g. smtp.gmail.com, smtp.office365.com)</div>
                                                        </div>
                                                        <div class="col-xl-3 col-md-3 mb-3">
                                                            <label class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                                            <select name="smtp_port" class="form-control default-select">
                                                                <option value="587" {{ ($settings['smtp_port'] ?? '587') == '587' ? 'selected' : '' }}>587 (TLS - Recommended)</option>
                                                                <option value="465" {{ ($settings['smtp_port'] ?? '') == '465' ? 'selected' : '' }}>465 (SSL)</option>
                                                                <option value="25" {{ ($settings['smtp_port'] ?? '') == '25' ? 'selected' : '' }}>25 (No Encryption)</option>
                                                                <option value="2525" {{ ($settings['smtp_port'] ?? '') == '2525' ? 'selected' : '' }}>2525 (Alternative)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-3 col-md-3 mb-3">
                                                            <label class="form-label">Encryption <span class="text-danger">*</span></label>
                                                            <select name="smtp_encryption" class="form-control default-select">
                                                                <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                                                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                                <option value="" {{ ($settings['smtp_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">SMTP Username <span class="text-danger">*</span></label>
                                                            <input type="text" name="smtp_username" class="form-control" placeholder="e.g. your@email.com" value="{{ $settings['smtp_username'] ?? '' }}" required>
                                                            <div class="form-text">Usually your full email address</div>
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">SMTP Password <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <input type="password" name="smtp_password" id="smtpPassword" class="form-control" placeholder="Enter SMTP password" value="{{ $settings['smtp_password'] ?? '' }}" required>
                                                                <button class="btn btn-outline-primary" type="button" onclick="togglePassword()">
                                                                    <i class="fas fa-eye" id="pwdToggleIcon"></i>
                                                                </button>
                                                            </div>
                                                            <div class="form-text">For Gmail, use an App Password (not your account password)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- From Address Settings --}}
                                        <div class="filter cm-content-box box-primary mt-4">
                                            <div class="content-title SlideToolHeader">
                                                <div class="cpa">
                                                    <i class="fas fa-user me-2"></i>Default From Address
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                                </div>
                                            </div>
                                            <div class="cm-content-body form excerpt">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">From Email <span class="text-danger">*</span></label>
                                                            <input type="email" name="from_email" class="form-control" placeholder="e.g. noreply@company.co.za" value="{{ $settings['from_email'] ?? '' }}" required>
                                                            <div class="form-text">Default sender email address</div>
                                                        </div>
                                                        <div class="col-xl-6 col-md-6 mb-3">
                                                            <label class="form-label">From Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="from_name" class="form-control" placeholder="e.g. SmartWeigh CIMS" value="{{ $settings['from_name'] ?? '' }}" required>
                                                            <div class="form-text">Default sender display name</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="mt-4 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Settings
                                            </button>
                                            <button type="button" class="btn btn-info light" onclick="testSmtp()">
                                                <i class="fas fa-paper-plane me-2"></i>Test Connection
                                            </button>
                                        </div>
                                    </form>

                                    {{-- SMTP Help Section --}}
                                    <div class="filter cm-content-box box-primary mt-4">
                                        <div class="content-title SlideToolHeader">
                                            <div class="cpa">
                                                <i class="fas fa-info-circle me-2"></i>SMTP Configuration Help
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                            </div>
                                        </div>
                                        <div class="cm-content-body form excerpt" style="display:none;">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Provider</th>
                                                                <th>SMTP Host</th>
                                                                <th>Port</th>
                                                                <th>Encryption</th>
                                                                <th>Notes</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Gmail</strong></td>
                                                                <td>smtp.gmail.com</td>
                                                                <td>587</td>
                                                                <td>TLS</td>
                                                                <td>Requires App Password</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Outlook/365</strong></td>
                                                                <td>smtp.office365.com</td>
                                                                <td>587</td>
                                                                <td>TLS</td>
                                                                <td>Use full email as username</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Yahoo</strong></td>
                                                                <td>smtp.mail.yahoo.com</td>
                                                                <td>587</td>
                                                                <td>TLS</td>
                                                                <td>Requires App Password</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Custom/cPanel</strong></td>
                                                                <td>mail.yourdomain.com</td>
                                                                <td>587</td>
                                                                <td>TLS</td>
                                                                <td>Check your hosting provider</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
function togglePassword() {
    var inp = document.getElementById('smtpPassword');
    var icon = document.getElementById('pwdToggleIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testSmtp() {
    var form = document.querySelector('form');
    var formData = new FormData(form);
    formData.append('test_connection', '1');

    fetch('{{ route("cimsemail.settings.test") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({icon:'success', title:'Connection Successful!', text: data.message, confirmButtonColor: 'var(--primary)'});
        } else {
            Swal.fire({icon:'error', title:'Connection Failed', text: data.message, confirmButtonColor: 'var(--primary)'});
        }
    })
    .catch(err => {
        Swal.fire({icon:'error', title:'Error', text: 'Failed to test connection: ' + err.message, confirmButtonColor: 'var(--primary)'});
    });
}

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
