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
                                @include('cims_email::emails.partials.sidebar', ['activeFolder' => $folder])
                                {{-- Client Filter --}}
                                <div class="mt-3 px-2">
                                    <label class="form-label" style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fas fa-filter me-1"></i> Filter by Client
                                    </label>
                                    <select class="form-control form-control-sm default-select sd_drop_class" data-live-search="true" data-size="8" title="All Clients" onchange="window.location='{{ route('cimsemail.index') }}?folder={{ $folder }}&client_id='+this.value">
                                        @foreach($clients as $c)
                                            <option value="{{ $c->client_id }}" {{ $clientFilter == $c->client_id ? 'selected' : '' }}>{{ $c->client_code }} - {{ $c->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-9 col-xxl-8 dlab-scroll height720">
                                <div>
                                    <div role="toolbar" class="toolbar ms-1 ms-sm-0 ms-xl-1 d-flex align-items-center">
                                        <div class="btn-group mb-1">
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                                <label class="form-check-label" for="checkAll"></label>
                                            </div>
                                        </div>
                                        <div class="btn-group mb-1">
                                            <button class="btn btn-primary light px-3" type="button" onclick="window.location.reload()"><i class="ti-reload"></i></button>
                                        </div>
                                        <div class="btn-group mb-1">
                                            <button aria-expanded="false" data-bs-toggle="dropdown"
                                                class="btn btn-primary px-3 light dropdown-toggle mx-2" type="button">More
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
                                                                <input type="checkbox" class="form-check-input" id="chk_{{ $email->id }}">
                                                                <label class="form-check-label" for="chk_{{ $email->id }}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="ms-2">
                                                            <button class="border-0 bg-transparent align-middle p-0">
                                                                @if($email->status == 'sent')
                                                                    <i class="fa fa-circle text-success" style="font-size:8px;" aria-hidden="true"></i>
                                                                @elseif($email->status == 'draft')
                                                                    <i class="fa fa-circle text-warning" style="font-size:8px;" aria-hidden="true"></i>
                                                                @elseif($email->status == 'failed')
                                                                    <i class="fa fa-circle text-danger" style="font-size:8px;" aria-hidden="true"></i>
                                                                @else
                                                                    <i class="fa fa-circle text-muted" style="font-size:8px;" aria-hidden="true"></i>
                                                                @endif
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <a href="{{ $email->folder == 'drafts' ? route('cimsemail.compose', ['draft_id' => $email->id]) : route('cimsemail.view', $email->id) }}" class="col-mail col-mail-2" style="text-decoration:none;">
                                                        <div class="subject">
                                                            <strong>{{ $toDisplay ?: '(no recipient)' }}</strong> -
                                                            {{ $email->subject ?: '(no subject)' }}
                                                            @if($email->client_id)
                                                                @php $cl = $clients->firstWhere('client_id', $email->client_id); @endphp
                                                                @if($cl)
                                                                    <span class="badge badge-sm light badge-info ms-1">{{ $cl->client_code }}</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <div class="date">
                                                            <span class="badge badge-sm light {{ $email->status == 'sent' ? 'badge-success' : ($email->status == 'draft' ? 'badge-warning' : ($email->status == 'failed' ? 'badge-danger' : 'badge-secondary')) }} me-1">{{ ucfirst($email->status) }}</span>
                                                            {{ $email->sent_at ? \Carbon\Carbon::parse($email->sent_at)->format('d M H:i') : \Carbon\Carbon::parse($email->created_at)->format('d M H:i') }}
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <i class="fas fa-envelope-open" style="font-size:48px;color:#ddd;"></i>
                                                <h5 class="mt-3" style="color:#888;">No emails in {{ $folder }}</h5>
                                                <p class="text-muted">
                                                    @if($folder == 'sent') You haven't sent any emails yet.
                                                    @elseif($folder == 'drafts') No saved drafts.
                                                    @elseif($folder == 'trash') Trash is empty.
                                                    @endif
                                                </p>
                                                <a href="{{ route('cimsemail.compose') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="fa fa-pen me-1"></i> Compose Email
                                                </a>
                                            </div>
                                        @endforelse
                                    </div>
                                    {{-- Pagination --}}
                                    @if($emails->hasPages())
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-3">
                                        <p class="mb-2 me-3 text-muted" style="font-size:12px;">Showing {{ $emails->firstItem() }}-{{ $emails->lastItem() }} of {{ $emails->total() }} emails</p>
                                        <nav>
                                            {{ $emails->appends(request()->query())->links() }}
                                        </nav>
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
