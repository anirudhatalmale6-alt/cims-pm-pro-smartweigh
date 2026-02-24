@extends('smartdash::layouts.default')

@section('title', 'View Document - ' . $document->title)

@push('styles')
<style>
.doc-detail-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}
.doc-detail-card .card-header {
    background: linear-gradient(135deg, #17A2B8, #138496);
    color: #fff;
    border-radius: 12px 12px 0 0;
    padding: 20px;
}
.doc-detail-card .card-body {
    padding: 25px;
}
.detail-label {
    font-weight: 600;
    color: #666;
    margin-bottom: 5px;
}
.detail-value {
    font-size: 1.1rem;
    margin-bottom: 15px;
}
.file-preview-box {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
}
.file-preview-box i {
    font-size: 64px;
    margin-bottom: 20px;
}
.btn-action-lg {
    padding: 12px 25px;
    font-size: 1rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row page-titles mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0)">CIMS</a></li>
                <li class="breadcrumb-item"><a href="/cims/docmanager">Document Manager</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
            <div>
                <a href="/cims/docmanager/{{ $document->id }}/edit" class="btn btn-warning me-2">
                    <i class="fa fa-edit me-2"></i> Edit
                </a>
                <a href="/cims/docmanager" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Document Details -->
        <div class="col-lg-8">
            <div class="doc-detail-card card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fa fa-file-alt me-2"></i>{{ $document->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Document Code</div>
                            <div class="detail-value">{{ $document->document_code ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Category</div>
                            <div class="detail-value">{{ $document->category->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Document Type</div>
                            <div class="detail-value">{{ $document->type->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Period</div>
                            <div class="detail-value">{{ $document->period_name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Client</div>
                            <div class="detail-value">{{ $document->client_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Client Code</div>
                            <div class="detail-value">{{ $document->client_code ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Registration Number</div>
                            <div class="detail-value">{{ $document->registration_number ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Client Email</div>
                            <div class="detail-value">{{ $document->client_email ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="detail-label">Issue Date</div>
                            <div class="detail-value">{{ $document->issue_date ? $document->issue_date->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Expiry Date</div>
                            <div class="detail-value">
                                @if($document->expiry_date)
                                    {{ $document->expiry_date->format('d M Y') }}
                                    @if($document->is_expired)
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($document->days_until_expiry <= 30)
                                        <span class="badge bg-warning">{{ $document->days_until_expiry }} days left</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="badge bg-{{ $document->status == 'Current' ? 'success' : 'secondary' }}">
                                    {{ $document->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($document->description)
                    <hr>
                    <div class="detail-label">Description</div>
                    <div class="detail-value">{{ $document->description }}</div>
                    @endif

                    @if($document->notes)
                    <hr>
                    <div class="detail-label">Notes</div>
                    <div class="detail-value">{{ $document->notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- File Preview -->
        <div class="col-lg-4">
            <div class="doc-detail-card card">
                <div class="card-body">
                    <div class="file-preview-box">
                        <i class="fa {{ $document->file_icon }}"></i>
                        <h5>{{ $document->file_original_name }}</h5>
                        <p class="text-muted">{{ $document->file_size_formatted }}</p>
                        <a href="/cims/docmanager/{{ $document->id }}/download" class="btn btn-primary btn-action-lg">
                            <i class="fa fa-download me-2"></i> Download
                        </a>
                    </div>

                    <hr>

                    <div class="detail-label">Uploaded By</div>
                    <div class="detail-value">{{ $document->uploaded_by ?? 'System' }}</div>

                    <div class="detail-label">Upload Date</div>
                    <div class="detail-value">{{ $document->created_at->format('d M Y, H:i') }}</div>

                    @if($document->updated_at && $document->updated_at != $document->created_at)
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value">{{ $document->updated_at->format('d M Y, H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
