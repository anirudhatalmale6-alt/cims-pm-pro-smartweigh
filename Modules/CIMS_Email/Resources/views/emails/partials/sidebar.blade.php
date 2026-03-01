<div class="email-left-box email-left-body">
    <div class="generic-width px-0 mb-5 mt-4 mt-sm-0">
        <div class="p-0">
            <a href="{{ route('cimsemail.compose') }}" class="btn btn-primary btn-block">Compose</a>
        </div>
        <div class="mail-list rounded mt-4">
            <a href="{{ route('cimsemail.sent') }}" class="list-group-item {{ ($activeFolder ?? '') == 'sent' ? 'active' : '' }}">
                <i class="fa fa-paper-plane font-18 align-middle me-2"></i> Sent
                <span class="badge badge-secondary badge-sm float-end">{{ $counts['sent'] ?? 0 }}</span>
            </a>
            <a href="{{ route('cimsemail.drafts') }}" class="list-group-item {{ ($activeFolder ?? '') == 'drafts' ? 'active' : '' }}">
                <i class="mdi mdi-file-document-box font-18 align-middle me-2"></i> Drafts
                <span class="badge badge-warning badge-sm float-end">{{ $counts['drafts'] ?? 0 }}</span>
            </a>
            <a href="{{ route('cimsemail.index', ['folder' => 'trash']) }}" class="list-group-item {{ ($activeFolder ?? '') == 'trash' ? 'active' : '' }}">
                <i class="fa fa-trash font-18 align-middle me-2"></i> Trash
                <span class="badge badge-danger text-white badge-sm float-end">{{ $counts['trash'] ?? 0 }}</span>
            </a>
        </div>
        <div class="mail-list rounded overflow-hidden mt-4">
            <div class="intro-title d-flex justify-content-between mt-0">
                <h5>Manage</h5>
            </div>
            <a href="{{ route('cimsemail.templates') }}" class="list-group-item {{ ($activePage ?? '') == 'templates' ? 'active' : '' }}">
                <span class="icon-warning"><i class="fa fa-circle" aria-hidden="true"></i></span> Templates
            </a>
            <a href="{{ route('cimsemail.signature') }}" class="list-group-item {{ ($activePage ?? '') == 'signature' ? 'active' : '' }}">
                <span class="icon-success"><i class="fa fa-circle" aria-hidden="true"></i></span> My Signature
            </a>
            <a href="{{ route('cimsemail.settings') }}" class="list-group-item {{ ($activePage ?? '') == 'settings' ? 'active' : '' }}">
                <span class="icon-primary"><i class="fa fa-circle" aria-hidden="true"></i></span> SMTP Settings
            </a>
        </div>
    </div>
</div>
