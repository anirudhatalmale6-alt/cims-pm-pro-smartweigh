@extends('layouts.default')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Email</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">{{ $draft ? 'Edit Draft' : 'Compose' }}</a></li>
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
                                            <a href="{{ route('cimsemail.templates') }}" class="list-group-item"><span class="icon-primary"><i
                                                        class="fa fa-circle" aria-hidden="true"></i></span>
                                                Templates</a>
                                        </div>
                                        {{-- Client Linking --}}
                                        <div class="mt-4">
                                            <label class="mb-2" style="font-size:12px;font-weight:700;color:#1a3c4d;text-transform:uppercase;letter-spacing:0.5px;">
                                                <i class="fas fa-link" style="margin-right:4px;"></i> Link to Client
                                            </label>
                                            <select name="client_id" form="composeForm" class="form-control form-control-sm default-select sd_drop_class" data-live-search="true" data-size="8" title="-- No Client --" id="ecClientSelect">
                                                @foreach($clients as $c)
                                                    <option value="{{ $c->client_id }}" {{ $selectedClientId == $c->client_id ? 'selected' : '' }}>{{ $c->client_code }} - {{ $c->company_name }}</option>
                                                @endforeach
                                            </select>
                                            <div id="ecClientContacts" class="mt-2"></div>
                                        </div>
                                        {{-- Template Selector --}}
                                        <div class="mt-4">
                                            <label class="mb-2" style="font-size:12px;font-weight:700;color:#1a3c4d;text-transform:uppercase;letter-spacing:0.5px;">
                                                <i class="fas fa-file-code" style="margin-right:4px;"></i> Template
                                            </label>
                                            <select class="form-control form-control-sm" id="ecTemplateSelect">
                                                <option value="">-- No Template --</option>
                                                @foreach($templates->groupBy('category') as $category => $tpls)
                                                    <optgroup label="{{ $category }}">
                                                        @foreach($tpls as $t)
                                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <h4 class="card-title d-sm-none d-block">Email</h4>
                                        <div class="email-tools-box float-end mb-2">
                                            <i class="fa-solid fa-list-ul"></i>
                                        </div>
                                    </div>

                                    <div class="compose-content">
                                        <form method="POST" action="{{ route('cimsemail.send') }}" enctype="multipart/form-data" id="composeForm">
                                            @csrf
                                            @if($draft)
                                                <input type="hidden" name="draft_id" value="{{ $draft->id }}">
                                            @endif
                                            <div class="mb-3">
                                                <input type="text" name="to_emails" id="ecTo" class="form-control bg-transparent"
                                                    placeholder=" To: (comma separated emails)"
                                                    value="{{ $draft ? implode(', ', json_decode($draft->to_emails, true) ?? []) : '' }}">
                                            </div>
                                            <div class="mb-3 d-flex align-items-center gap-2">
                                                <input type="text" name="cc_emails" id="ecCc" class="form-control bg-transparent d-none"
                                                    placeholder=" CC:"
                                                    value="{{ $draft ? implode(', ', json_decode($draft->cc_emails, true) ?? []) : '' }}">
                                                <input type="text" name="bcc_emails" id="ecBcc" class="form-control bg-transparent d-none"
                                                    placeholder=" BCC:"
                                                    value="{{ $draft ? implode(', ', json_decode($draft->bcc_emails, true) ?? []) : '' }}">
                                                <a href="javascript:void(0)" class="text-primary" style="font-size:12px;font-weight:600;white-space:nowrap;" onclick="toggleCcBcc()">CC / BCC</a>
                                            </div>
                                            <div class="mb-3">
                                                <input type="text" name="subject" id="ecSubject" class="form-control bg-transparent"
                                                    placeholder=" Subject:"
                                                    value="{{ $draft->subject ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <textarea id="ecBody" name="body_html" class="textarea_editor form-control bg-transparent" rows="8"
                                                    placeholder="Write your email here..." style="display:none;">{{ $draft->body_html ?? '' }}</textarea>
                                            </div>
                                        </form>
                                        <h5 class="mb-4"><i class="fa fa-paperclip"></i> Attachments</h5>
                                        <div class="mb-3 p-3" style="border:1.5px dashed #ddd;border-radius:6px;text-align:center;">
                                            <input name="attachments[]" form="composeForm" type="file" multiple id="ecAttachInput" onchange="showAttachments(this)">
                                            <div id="ecAttachList" class="mt-2 d-flex flex-wrap gap-2"></div>
                                        </div>
                                    </div>
                                    <div class="text-start mt-4 mb-3">
                                        <button class="btn btn-primary btn-sl-sm me-2" type="submit" form="composeForm"><span
                                                class="me-2"><i class="fa fa-paper-plane"></i></span>Send</button>
                                        <button class="btn btn-warning light btn-sl-sm me-2" type="button" onclick="saveDraft()"><span
                                                class="me-2"><i class="fa fa-save"></i></span>Save Draft</button>
                                        <a href="{{ route('cimsemail.index') }}" class="btn btn-danger light btn-sl-sm"><span
                                                class="me-2"><i class="fa fa-times"></i></span>Discard</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Summernote
    $('#ecBody').summernote({
        height: 350,
        placeholder: 'Write your email here...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview']],
        ],
        fontNames: ['Arial', 'Helvetica', 'Courier New', 'Georgia', 'Times New Roman', 'Verdana', 'Tahoma'],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36'],
        callbacks: {
            onInit: function() {
                $('.note-editable').css({
                    'font-family': 'Arial, sans-serif',
                    'font-size': '14px',
                    'padding': '20px'
                });
            }
        }
    });

    // Load client contacts on selection
    $('#ecClientSelect').on('changed.bs.select', function() {
        var clientId = $(this).val();
        if (!clientId) {
            $('#ecClientContacts').empty();
            return;
        }
        $.get('{{ url("cims/email/ajax/client-contacts") }}/' + clientId, function(contacts) {
            var html = '';
            contacts.forEach(function(c) {
                html += '<span class="badge badge-sm light badge-info me-1 mb-1" style="cursor:pointer;font-size:10px;" onclick="addToField(\'' + c.email + '\')" title="Click to add to To field">';
                html += '<i class="fas fa-user me-1"></i> ' + c.name + ' (' + c.email + ')';
                html += '</span>';
            });
            $('#ecClientContacts').html(html);
        });
    });

    // Trigger on page load if client pre-selected
    @if($selectedClientId)
    setTimeout(function() {
        $('#ecClientSelect').trigger('changed.bs.select');
    }, 500);
    @endif

    // Load template
    $('#ecTemplateSelect').on('change', function() {
        var tplId = $(this).val();
        if (!tplId) return;
        $.get('{{ url("cims/email/templates") }}/' + tplId + '/load', function(tpl) {
            $('#ecSubject').val(tpl.subject);
            $('#ecBody').summernote('code', tpl.body_html);
        });
    });
});

function addToField(email) {
    var current = $('#ecTo').val();
    if (current && current.indexOf(email) !== -1) return;
    if (current) {
        $('#ecTo').val(current + ', ' + email);
    } else {
        $('#ecTo').val(email);
    }
}

function toggleCcBcc() {
    $('#ecCc, #ecBcc').toggleClass('d-none');
}

function showAttachments(input) {
    var html = '';
    for (var i = 0; i < input.files.length; i++) {
        html += '<span class="badge badge-sm light badge-secondary"><i class="fas fa-file me-1"></i> ' + input.files[i].name + '</span>';
    }
    document.getElementById('ecAttachList').innerHTML = html;
}

function saveDraft() {
    var form = document.getElementById('composeForm');
    form.action = '{{ route("cimsemail.save-draft") }}';
    form.submit();
}
</script>
@endpush

@endsection
