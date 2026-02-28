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
                                <div class="email-left-box email-left-body">
                                    <div class="generic-width px-0 mb-5 mt-4 mt-sm-0">
                                        <div class="p-0">
                                            <a href="{{ route('cimsemail.compose') }}" class="btn btn-primary btn-block">Compose</a>
                                        </div>
                                        <div class="mail-list rounded mt-4">
                                            <a href="{{ route('cimsemail.sent') }}" class="list-group-item"><i
                                                    class="fa fa-paper-plane font-18 align-middle me-2"></i> Sent</a>
                                            <a href="{{ route('cimsemail.drafts') }}" class="list-group-item"><i
                                                    class="mdi mdi-file-document-box font-18 align-middle me-2"></i> Drafts</a>
                                            <a href="{{ route('cimsemail.index', ['folder' => 'trash']) }}" class="list-group-item"><i
                                                    class="fa fa-trash font-18 align-middle me-2"></i> Trash</a>
                                        </div>
                                        <div class="mail-list rounded overflow-hidden mt-4">
                                            <div class="intro-title d-flex justify-content-between mt-0">
                                                <h5>Manage</h5>
                                            </div>
                                            <a href="{{ route('cimsemail.templates') }}" class="list-group-item active"><span class="icon-primary"><i
                                                        class="fa fa-circle" aria-hidden="true"></i></span>
                                                Templates</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h4 class="card-title mb-0"><i class="fas fa-file-code me-2 text-primary"></i>Email Templates</h4>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal" onclick="resetTemplateForm()">
                                            <i class="fas fa-plus me-1"></i> New Template
                                        </button>
                                    </div>

                                    <div class="row">
                                        @forelse($templates as $tpl)
                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4">
                                            <div class="card border" style="margin-bottom:0;">
                                                <div class="card-header d-flex align-items-center justify-content-between" style="background:#f8f9fa;">
                                                    <h5 class="mb-0" style="font-size:14px;">{{ $tpl->name }}</h5>
                                                    <span class="badge badge-sm light badge-primary">{{ $tpl->category }}</span>
                                                </div>
                                                <div class="card-body py-3">
                                                    <p class="mb-1" style="font-size:12px;color:#666;"><strong>Subject:</strong> {{ $tpl->subject }}</p>
                                                    <p class="mb-0" style="font-size:11px;color:#999;">{{ Str::limit(strip_tags($tpl->body_html), 120) }}</p>
                                                </div>
                                                <div class="card-footer d-flex gap-2" style="background:#fff;">
                                                    <a href="{{ route('cimsemail.compose') }}?template_load={{ $tpl->id }}" class="btn btn-primary btn-sm light">
                                                        <i class="fas fa-pen me-1"></i> Use
                                                    </a>
                                                    <button type="button" class="btn btn-info btn-sm light" onclick="editTemplate({{ json_encode($tpl) }})">
                                                        <i class="fas fa-edit me-1"></i> Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('cimsemail.templates.delete', $tpl->id) }}" style="display:inline;" onsubmit="return confirm('Delete this template?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm light"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="col-12 text-center py-5">
                                            <i class="fas fa-file-code" style="font-size:48px;color:#ddd;"></i>
                                            <h5 class="mt-3" style="color:#888;">No templates yet</h5>
                                            <p class="text-muted">Create your first email template to get started.</p>
                                        </div>
                                        @endforelse
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
                    <div class="p-2" style="background:#f8f9fa;border-radius:4px;font-size:11px;color:#888;">
                        <strong>Merge Fields:</strong> {client_name}, {company_name}, {tax_number}, {user_name}, {month}, {year}
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
</script>
@endpush

@endsection
