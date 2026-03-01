@extends('layouts.default')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Email</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Read</a></li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-xxl-4">
                                @include('cims_email::emails.partials.sidebar')
                            </div>
                            <div class="col-xl-9 col-xxl-8">
                                <div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="right-box-padding">
                                                <div class="toolbar mb-4" role="toolbar">
                                                    <div class="btn-group mb-1">
                                                        <a href="{{ route('cimsemail.sent') }}" class="btn btn-primary light px-3"><i class="fa fa-arrow-left"></i></a>
                                                    </div>
                                                    <div class="btn-group mb-1">
                                                        <a href="{{ route('cimsemail.compose', ['client_id' => $email->client_id]) }}" class="btn btn-primary light px-3" data-bs-toggle="tooltip" data-bs-title="New Email"><i class="fa fa-pen"></i></a>
                                                    </div>
                                                    <div class="btn-group mb-1">
                                                        <form method="POST" action="{{ route('cimsemail.trash', $email->id) }}" style="display:inline;" onsubmit="return confirm('Move to trash?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary light px-3" data-bs-toggle="tooltip" data-bs-title="Delete"><i class="fa fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                    <div class="btn-group mb-1">
                                                        <button type="button"
                                                            class="btn btn-primary light dropdown-toggle"
                                                            data-bs-toggle="dropdown">More <span
                                                                class="caret m-l-5"></span>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="{{ route('cimsemail.compose', ['client_id' => $email->client_id]) }}">New Email to Client</a>
                                                        </div>
                                                    </div>
                                                    <div class="email-tools-box float-end">
                                                        <i class="fa-solid fa-list-ul"></i>
                                                    </div>
                                                </div>
                                                <div class="read-content">
                                                    <div class="media pt-3 d-sm-flex d-block justify-content-between">
                                                        <div class="clearfix mb-3 d-flex">
                                                            @php
                                                                $toList = json_decode($email->to_emails, true) ?? [];
                                                                $initials = strtoupper(substr($toList[0] ?? 'E', 0, 2));
                                                            @endphp
                                                            <div class="me-3 rounded d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:linear-gradient(135deg,var(--primary),#148f9f);color:#fff;font-weight:700;font-size:22px;border-radius:50% !important;">
                                                                {{ $initials }}
                                                            </div>
                                                            <div class="media-body me-2">
                                                                <h5 class="text-primary mb-0 mt-1">{{ implode(', ', $toList) }}</h5>
                                                                <p class="mb-0">
                                                                    {{ $email->sent_at ? \Carbon\Carbon::parse($email->sent_at)->format('d M Y, H:i') : \Carbon\Carbon::parse($email->created_at)->format('d M Y, H:i') }}
                                                                </p>
                                                                <span class="badge badge-sm light {{ $email->status == 'sent' ? 'badge-success' : ($email->status == 'failed' ? 'badge-danger' : 'badge-warning') }} mt-1">{{ ucfirst($email->status) }}</span>
                                                                @if($client)
                                                                    <span class="badge badge-sm light badge-info mt-1"><i class="fas fa-link me-1"></i>{{ $client->client_code }} - {{ $client->company_name }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="clearfix mb-3">
                                                            <a href="{{ route('cimsemail.compose', ['client_id' => $email->client_id]) }}" data-bs-toggle="tooltip"
                                                                data-bs-title="Reply"
                                                                class="btn btn-primary px-3 my-1 light me-2"><i
                                                                    class="fa fa-reply"></i></a>
                                                            <form method="POST" action="{{ route('cimsemail.trash', $email->id) }}" style="display:inline;" onsubmit="return confirm('Move to trash?')">
                                                                @csrf
                                                                <button type="submit" data-bs-toggle="tooltip"
                                                                    data-bs-title="Delete"
                                                                    class="btn btn-primary px-3 my-1 light"><i
                                                                        class="fa fa-trash"></i></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="media mb-2 mt-3">
                                                        <div class="media-body">
                                                            <span class="pull-end">
                                                                {{ $email->sent_at ? \Carbon\Carbon::parse($email->sent_at)->format('h:i A') : '' }}
                                                            </span>
                                                            <h5 class="my-1 text-primary">{{ $email->subject ?: '(no subject)' }}</h5>
                                                            <p class="read-content-email">
                                                                From: {{ $email->from_name }} &lt;{{ $email->from_email }}&gt;
                                                                <br>To: {{ implode(', ', $toList) }}
                                                                @php $cc = json_decode($email->cc_emails, true) ?? []; @endphp
                                                                @if(count($cc) > 0)
                                                                    <br>CC: {{ implode(', ', $cc) }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="read-content-body default-height">
                                                        {!! $email->body_html !!}
                                                    </div>

                                                    @if($attachments->count() > 0)
                                                    <hr>
                                                    <div class="mb-3">
                                                        <h6 class="text-primary"><i class="fas fa-paperclip me-2"></i>Attachments ({{ $attachments->count() }})</h6>
                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                            @foreach($attachments as $att)
                                                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-primary light btn-sm" style="text-decoration:none;">
                                                                    <i class="fas fa-file me-1"></i>
                                                                    {{ $att->original_filename }}
                                                                    <span class="text-muted ms-1">({{ number_format($att->file_size / 1024, 1) }} KB)</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <hr>
                                                    <div class="read-content-attachment">
                                                        <a href="{{ route('cimsemail.compose', ['client_id' => $email->client_id]) }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-reply me-1"></i>New Email</a>
                                                    </div>
                                                    <hr>
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
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
</script>
@endpush
