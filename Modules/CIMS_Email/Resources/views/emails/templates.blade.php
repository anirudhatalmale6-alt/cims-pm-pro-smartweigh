@extends('layouts.default')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Email</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Templates</a></li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-xxl-4">
                                @include('cims_email::emails.partials.sidebar', ['activePage' => 'templates'])
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h4 class="card-title mb-0"><i class="fas fa-file-code me-2 text-primary"></i>Email Templates</h4>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#templateModal" onclick="resetTemplateForm()">
                                            <i class="fas fa-plus me-1"></i> New Template
                                        </button>
                                    </div>

                                    {{-- Templates Table (Fillow CMS style) --}}
                                    <div class="filter cm-content-box box-primary">
                                        <div class="content-title SlideToolHeader">
                                            <div class="cpa">
                                                <i class="fa-solid fa-envelope me-1"></i> Template List
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
                                            </div>
                                        </div>
                                        <div class="cm-content-body form excerpt">
                                            <div class="card-body pb-4">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Name</th>
                                                                <th>Category</th>
                                                                <th>Subject</th>
                                                                <th>Status</th>
                                                                <th class="pe-4">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($templates as $idx => $tpl)
                                                            <tr>
                                                                <td>{{ $idx + 1 }}</td>
                                                                <td><strong>{{ $tpl->name }}</strong></td>
                                                                <td><span class="badge badge-sm light badge-primary">{{ $tpl->category }}</span></td>
                                                                <td>{{ Str::limit($tpl->subject, 40) }}</td>
                                                                <td>
                                                                    @if($tpl->is_active)
                                                                        <span class="badge badge-success light">Active</span>
                                                                    @else
                                                                        <span class="badge badge-danger light">Inactive</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-nowrap">
                                                                    <a href="{{ route('cimsemail.compose') }}?template_load={{ $tpl->id }}" class="btn btn-primary btn-sm content-icon" data-bs-toggle="tooltip" data-bs-title="Use">
                                                                        <i class="fa fa-paper-plane"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-warning btn-sm content-icon" onclick="editTemplate({{ json_encode($tpl) }})" data-bs-toggle="tooltip" data-bs-title="Edit">
                                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                                    </button>
                                                                    <form method="POST" action="{{ route('cimsemail.templates.delete', $tpl->id) }}" style="display:inline;" onsubmit="return confirm('Delete this template?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm content-icon me-0" data-bs-toggle="tooltip" data-bs-title="Delete">
                                                                            <i class="fa-solid fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center py-4 text-muted">
                                                                    <i class="fas fa-file-code" style="font-size:36px;color:#ddd;display:block;margin-bottom:10px;"></i>
                                                                    No templates yet. Create your first email template!
                                                                </td>
                                                            </tr>
                                                            @endforelse
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

{{-- Template Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle"><i class="fas fa-file-code me-2"></i> New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="templateForm" action="{{ route('cimsemail.templates.store') }}">
                @csrf
                <div id="templateMethodField"></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label" style="font-weight:600;">Template Name</label>
                            <input type="text" name="name" id="tplName" class="form-control" required placeholder="e.g. Welcome Letter">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight:600;">Category</label>
                            <select name="category" id="tplCategory" class="form-control">
                                <option value="General">General</option>
                                <option value="Compliance">Compliance</option>
                                <option value="Invoicing">Invoicing</option>
                                <option value="Reminders">Reminders</option>
                                <option value="Notices">Notices</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;">Subject Line</label>
                        <input type="text" name="subject" id="tplSubject" class="form-control" required placeholder="Email subject...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;">Body</label>
                        <textarea name="body_html" id="tplBody"></textarea>
                    </div>
                    <div class="new-scroll p-3" style="background:#f8f9fa;border-radius:6px;">
                        <h6 class="mb-2" style="font-size:12px;font-weight:700;text-transform:uppercase;">Merge Fields (Placeholders)</h6>
                        <div class="d-grid mb-2">
                            <span style="font-size:11px;color:#666;"><strong>{client_name}</strong> - Client contact name</span>
                            <span style="font-size:11px;color:#666;"><strong>{company_name}</strong> - Company / Trading name</span>
                            <span style="font-size:11px;color:#666;"><strong>{tax_number}</strong> - Tax reference number</span>
                            <span style="font-size:11px;color:#666;"><strong>{user_name}</strong> - Logged-in user name</span>
                            <span style="font-size:11px;color:#666;"><strong>{month}</strong> - Current month</span>
                            <span style="font-size:11px;color:#666;"><strong>{year}</strong> - Current year</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

@push('scripts')
<script>
var tplEditorInitialized = false;

$('#templateModal').on('shown.bs.modal', function() {
    if (!tplEditorInitialized) {
        $('#tplBody').summernote({
            height: 250,
            placeholder: 'Design your email template...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['codeview']],
            ]
        });
        tplEditorInitialized = true;
    }
});

function resetTemplateForm() {
    document.getElementById('templateModalTitle').innerHTML = '<i class="fas fa-file-code me-2"></i> New Template';
    document.getElementById('templateForm').action = '{{ route("cimsemail.templates.store") }}';
    document.getElementById('templateMethodField').innerHTML = '';
    document.getElementById('tplName').value = '';
    document.getElementById('tplSubject').value = '';
    document.getElementById('tplCategory').value = 'General';
    if (tplEditorInitialized) $('#tplBody').summernote('code', '');
}

function editTemplate(tpl) {
    document.getElementById('templateModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i> Edit Template';
    document.getElementById('templateForm').action = '{{ url("cims/email/templates") }}/' + tpl.id;
    document.getElementById('templateMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('tplName').value = tpl.name;
    document.getElementById('tplSubject').value = tpl.subject;
    document.getElementById('tplCategory').value = tpl.category;
    if (tplEditorInitialized) {
        $('#tplBody').summernote('code', tpl.body_html || '');
    }
    var modal = new bootstrap.Modal(document.getElementById('templateModal'));
    modal.show();
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

// Init tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
</script>
@endpush

@endsection
