@extends('layouts.default')

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
    {{-- <div class="row page-titles mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0)">CIMS</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cimsdocmanager.index') }}">Document Manager</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
            <div>
                <x-edit-button href="{{ route('cimsdocmanager.edit',$document->id) }}" ></x-edit-button>
                <x-close-button href="{{ route('client.edit', $client) }}" ></x-close-button>
            </div>
        </div>
    </div> --}}

    <x-primary-breadcrumb
        title="Document Manager"
        subtitle="Manage all your documents in one place"
        icon="fa-solid fa-file-alt"
        :breadcrumbs="[
            ['label' => '<i class=\'fa fa-home\'></i> CIMS', 'url' => url('/')],
            ['label' => 'Document Manager','url' => route('cimsdocmanager.index')],
            ['label' =>  'Edit'],
        ]"
    >
        <x-slot:actions>
           {{-- <x-edit-button href="{{ route('cimsdocmanager.edit',$document->id) }}" ></x-edit-button> --}}
           
           {{-- <x-close-button href="{{ route('client.edit', $client) }}" ></x-close-button> --}}
           <x-close-button href="{{ url()->previous() }}" ></x-close-button>
        </x-slot:actions>
    </x-primary-breadcrumb>

    <div class="row">
        <!-- Document Details -->
        <div class="col-lg-8">
            <div class="doc-detail-card card">
                <div class="card-header sd_background_pink">
                    <h4 class="mb-0 text-white"><i class="fa fa-file-alt me-2"></i>{{ $document->file_stored_name }}</h4>
                </div>
                <div class="card-body">
                    @php
                        $file_url = asset('storage/' . $document->file_path);
                        $file_extension = strtolower(pathinfo($document->file_stored_name, PATHINFO_EXTENSION));
                    @endphp

                    @if(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ $file_url }}" style="width:100%; height:auto;" alt="{{ $document->file_stored_name }}">
                    @elseif(in_array($file_extension, ['mp4', 'm4a', 'webm']))
                        <video controls style="width:100%; height:auto;">
                            <source src="{{ $file_url }}" type="{{ $document->file_mime_type }}">
                            Your browser does not support the video tag.
                        </video>
                    @elseif($file_extension == 'mp3')
                        <audio controls style="width:100%;">
                            <source src="{{ $file_url }}" type="{{ $document->file_mime_type ?: 'audio/mpeg' }}">
                            Your browser does not support the audio element.
                        </audio>
                    @elseif($file_extension == 'pdf')
                        <iframe src="{{ $file_url }}" style="width:100%; height:500px;" frameborder="0"></iframe>
                    @elseif(in_array($file_extension, ['doc', 'docx', 'xls', 'xlsx', 'pptx']))
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($file_url) }}" style="width:100%; height:500px;" frameborder="0"></iframe>
                    @else
                        <p class="text-muted text-center">Unsupported file type for preview.</p>
                    @endif
                </div>
            </div>
        </div>


         <div class="col-lg-4">
            <div class="doc-detail-card card">
                <div class="card-body">
                    <div class="file-preview-box">
                        <img src="{{ asset('assets/images/cims_core/pdf.png') }}" width="100px" class="mb-3"/>
                        {{-- @if($file_extension === 'pdf')
                            -o
                        @elseif(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            -image
                        @elseif(in_array($file_extension, ['mp4', 'm4a', 'webm']))
                            -video
                        @elseif($file_extension === 'mp3')
                            -audio
                        @elseif(in_array($file_extension, ['doc', 'docx', 'xls', 'xlsx', 'pptx']))
                            -word
                        @else
                            -file
                        @endif --}}
                        <h5>{{ $document->file_stored_name }}</h5>
                        <p class="text-muted">
                            @php
                                $bytes = $document->file_size;
                                if ($bytes >= 1073741824) {
                                    $formatted = number_format($bytes / 1073741824, 2) . ' GB';
                                } elseif ($bytes >= 1048576) {
                                    $formatted = number_format($bytes / 1048576, 2) . ' MB';
                                } elseif ($bytes >= 1024) {
                                    $formatted = number_format($bytes / 1024, 2) . ' KB';
                                } else {
                                    $formatted = $bytes . ' bytes';
                                }
                            @endphp
                            {{ $formatted }}
                        </p>
                        <a href="{{ route('cimsdocmanager.download',$document->id)}}" class="btn btn-blue btn-action-lg">
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
