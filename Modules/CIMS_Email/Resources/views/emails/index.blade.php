@extends('layouts.default')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Email</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">{{ ucfirst($folder) }}</a></li>
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
                                            <a href="{{ route('cimsemail.sent') }}" class="list-group-item {{ $folder == 'sent' ? 'active' : '' }}"><i
                                                    class="fa fa-paper-plane font-18 align-middle me-2"></i> Sent <span
                                                    class="badge badge-secondary badge-sm float-end">{{ $counts['sent'] ?? 0 }}</span></a>
                                            <a href="{{ route('cimsemail.drafts') }}" class="list-group-item {{ $folder == 'drafts' ? 'active' : '' }}"><i
                                                    class="mdi mdi-file-document-box font-18 align-middle me-2"></i> Drafts <span
                                                    class="badge badge-warning badge-sm float-end">{{ $counts['drafts'] ?? 0 }}</span></a>
                                            <a href="{{ route('cimsemail.index', ['folder' => 'trash']) }}" class="list-group-item {{ $folder == 'trash' ? 'active' : '' }}"><i
                                                    class="fa fa-trash font-18 align-middle me-2"></i> Trash <span
                                                    class="badge badge-danger text-white badge-sm float-end">{{ $counts['trash'] ?? 0 }}</span></a>
                                        </div>
                                        <div class="mail-list rounded overflow-hidden mt-4">
                                            <div class="intro-title d-flex justify-content-between mt-0">
                                                <h5>Manage</h5>
                                            </div>
                                            <a href="{{ route('cimsemail.templates') }}" class="list-group-item"><span class="icon-primary"><i
                                                        class="fa fa-circle" aria-hidden="true"></i></span>
                                                Templates</a>
                                        </div>
                                        {{-- Client Filter --}}
                                        <div class="mt-4">
                                            <label class="mb-2" style="font-size:12px;font-weight:700;color:#1a3c4d;text-transform:uppercase;letter-spacing:0.5px;">
                                                <i class="fas fa-filter" style="margin-right:4px;"></i> Filter by Client
                                            </label>
                                            <select name="client_id" class="form-control form-control-sm default-select sd_drop_class" data-live-search="true" data-size="8" title="All Clients" onchange="window.location='{{ route('cimsemail.index') }}?folder={{ $folder }}&client_id='+this.value">
                                                @foreach($clients as $c)
                                                    <option value="{{ $c->client_id }}" {{ $clientFilter == $c->client_id ? 'selected' : '' }}>{{ $c->client_code }} - {{ $c->company_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div role="toolbar" class="toolbar ms-1 ms-sm-0 ms-xl-1 d-flex align-items-center">
                                        <div class="btn-group mb-1">
                                            <button class="btn btn-primary light px-3" type="button" onclick="window.location.reload()"><i class="ti-reload"></i></button>
                                        </div>
                                        <div class="btn-group mb-1">
                                            <button aria-expanded="false" data-bs-toggle="dropdown"
                                                class="btn btn-primary px-3 light dropdown-toggle mx-2" type="button">
                                                @if($folder == 'sent') <i class="fa fa-paper-plane me-1"></i> Sent
                                                @elseif($folder == 'drafts') <i class="fas fa-file-pen me-1"></i> Drafts
                                                @elseif($folder == 'trash') <i class="fa fa-trash me-1"></i> Trash
                                                @endif
                                                <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="{{ route('cimsemail.sent') }}" class="dropdown-item">Sent</a>
                                                <a href="{{ route('cimsemail.drafts') }}" class="dropdown-item">Drafts</a>
                                                <a href="{{ route('cimsemail.index', ['folder' => 'trash']) }}" class="dropdown-item">Trash</a>
                                            </div>
                                        </div>
                                        <div class="email-tools-box">
                                            <i class="fa-solid fa-list-ul"></i>
                                        </div>
                                        <form class="d-none d-sm-block ms-auto" method="GET" action="{{ route('cimsemail.index') }}">
                                            <input type="hidden" name="folder" value="{{ $folder }}">
                                            <div class="input-group ms-auto w-100 d-sm-inline-flex d-none">
                                                <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search emails...">
                                                <span class="input-group-text"><button class="bg-transparent border-0" type="submit"><i class="flaticon-381-search-2"></i></button></span>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="email-list mt-3">
                                        @forelse($emails as $email)
                                            @php
                                                $toList = json_decode($email->to_emails, true) ?? [];
                                                $toDisplay = implode(', ', array_slice($toList, 0, 2));
                                                if (count($toList) > 2) $toDisplay .= ' +' . (count($toList) - 2);
                                            @endphp
                                            <div class="message">
                                                <div>
                                                    <div class="d-flex message-single">
                                                        <div class="ps-1 align-self-center">
                                                            <div class="form-check custom-checkbox">
                                                                <input type="checkbox" class="form-check-input" id="checkbox_{{ $email->id }}">
                                                                <label class="form-check-label" for="checkbox_{{ $email->id }}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="ms-2">
                                                            @if($email->status == 'sent')
                                                                <span style="color:#28a745;"><i class="fa fa-circle" style="font-size:8px;" aria-hidden="true"></i></span>
                                                            @elseif($email->status == 'draft')
                                                                <span style="color:#ffc107;"><i class="fa fa-circle" style="font-size:8px;" aria-hidden="true"></i></span>
                                                            @elseif($email->status == 'failed')
                                                                <span style="color:#dc3545;"><i class="fa fa-circle" style="font-size:8px;" aria-hidden="true"></i></span>
                                                            @else
                                                                <span style="color:#999;"><i class="fa fa-circle" style="font-size:8px;" aria-hidden="true"></i></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <a href="{{ $email->folder == 'drafts' ? route('cimsemail.compose', ['draft_id' => $email->id]) : route('cimsemail.view', $email->id) }}" class="col-mail col-mail-2" style="text-decoration:none;">
                                                        <div class="subject">
                                                            <strong style="color:#1a3c4d;">{{ $toDisplay ?: '(no recipient)' }}</strong> -
                                                            {{ $email->subject ?: '(no subject)' }}
                                                            <span class="ms-2" style="font-size:11px;color:#999;">{{ Str::limit($email->body_text, 50) }}</span>
                                                            @if($email->client_id)
                                                                @php $cl = $clients->firstWhere('client_id', $email->client_id); @endphp
                                                                @if($cl)
                                                                    <span class="badge badge-sm light badge-info ms-2" style="font-size:9px;">{{ $cl->client_code }}</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <div class="date">
                                                            <span class="badge badge-sm light {{ $email->status == 'sent' ? 'badge-success' : ($email->status == 'draft' ? 'badge-warning' : ($email->status == 'failed' ? 'badge-danger' : 'badge-secondary')) }} me-2">{{ ucfirst($email->status) }}</span>
                                                            @if($email->sent_at)
                                                                {{ \Carbon\Carbon::parse($email->sent_at)->format('d M Y H:i') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($email->created_at)->format('d M Y H:i') }}
                                                            @endif
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <i class="fas fa-envelope-open" style="font-size:48px;color:#ddd;"></i>
                                                <h5 class="mt-3" style="color:#888;">No emails found</h5>
                                                <p class="text-muted">
                                                    @if($folder == 'sent') You haven't sent any emails yet.
                                                    @elseif($folder == 'drafts') No saved drafts.
                                                    @elseif($folder == 'trash') Trash is empty.
                                                    @endif
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>
                                    {{-- Pagination --}}
                                    @if($emails->hasPages())
                                    <div class="d-flex justify-content-center mt-3 mb-2">
                                        {{ $emails->appends(request()->query())->links() }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
