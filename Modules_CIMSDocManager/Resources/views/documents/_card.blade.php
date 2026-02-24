@php
    $cardClass = 'doc-card';
    if (isset($expiring) && $expiring) $cardClass .= ' expiring-card';
    if (isset($expired) && $expired) $cardClass .= ' expired-card';

    // Determine file icon class
    $iconClass = 'fa-file other';
    $ext = strtolower(pathinfo($doc->file_path ?? '', PATHINFO_EXTENSION));
    if (in_array($ext, ['pdf'])) $iconClass = 'fa-file-pdf pdf';
    elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'fa-file-word word';
    elseif (in_array($ext, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel excel';
    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'fa-file-image image';
@endphp

<div class="{{ $cardClass }}"
     data-title="{{ strtolower($doc->title ?? '') }}"
     data-client="{{ strtolower($doc->client_name ?? '') }}"
     data-category="{{ strtolower($doc->category->name ?? '') }}"
     data-code="{{ strtolower($doc->document_code ?? '') }}">
    <div class="row align-items-center">
        <!-- File Type Icon -->
        <div class="col-auto text-center pe-4">
            <i class="fa-solid {{ $iconClass }} doc-type-icon"></i>
        </div>

        <!-- Document Info -->
        <div class="col-lg-3 col-md-4 mb-3 mb-lg-0">
            <div class="doc-code">
                {{ $doc->document_code ?? 'DOC-000' }}
                @if(isset($showStatus) && $showStatus)
                    @if($doc->is_expired)
                        <span class="badge bg-danger">Expired</span>
                    @elseif($doc->days_until_expiry !== null && $doc->days_until_expiry <= 30)
                        <span class="badge bg-warning">Expiring</span>
                    @else
                        <span class="badge bg-success">Current</span>
                    @endif
                @endif
            </div>
            <div class="doc-title">{{ $doc->title }}</div>
            <div class="doc-client">
                <i class="fa fa-building me-1"></i> {{ $doc->client_name ?? 'No Client' }}
                @if($doc->client_code)
                    <span class="text-muted">({{ $doc->client_code }})</span>
                @endif
            </div>
            <div class="doc-date mt-2">
                <i class="fa fa-calendar"></i> Uploaded: {{ $doc->created_at->format('d M Y') }}
            </div>
        </div>

        <!-- Category & Period -->
        <div class="col-lg-2 col-md-4 col-6 mb-3 mb-lg-0">
            <div class="info-block">
                <div class="info-icon category">
                    <i class="fa fa-folder"></i>
                </div>
                <div>
                    <div class="info-label">Category</div>
                    <div class="info-value">{{ $doc->category->name ?? 'Uncategorized' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-6 mb-3 mb-lg-0">
            <div class="info-block">
                <div class="info-icon period">
                    <i class="fa fa-calendar-days"></i>
                </div>
                <div>
                    <div class="info-label">Period</div>
                    <div class="info-value">{{ $doc->period_name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Expiry Info -->
        <div class="col-lg-2 col-md-4 col-6 mb-3 mb-lg-0">
            <div class="info-block">
                <div class="info-icon expiry">
                    <i class="fa fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="info-label">Expiry</div>
                    @if($doc->expiry_date)
                        <div class="info-value">{{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}</div>
                        @if($doc->is_expired)
                            <div class="info-sub text-danger"><i class="fa fa-exclamation-circle"></i> Expired</div>
                        @elseif($doc->days_until_expiry !== null && $doc->days_until_expiry <= 30)
                            <div class="info-sub text-warning"><i class="fa fa-clock"></i> {{ $doc->days_until_expiry }} days left</div>
                        @endif
                    @else
                        <div class="info-value">No Expiry</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-lg-2 col-md-4 col-6 text-end">
            <div class="d-flex justify-content-end align-items-center gap-2">
                <!-- Quick Download Button -->
                <a href="/cims/docmanager/{{ $doc->id }}/download" class="btn btn-sm btn-success" title="Download">
                    <i class="fa fa-download"></i>
                </a>

                <!-- Three-dot Menu -->
                <div class="dropdown action-dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="/cims/docmanager/{{ $doc->id }}">
                                <i class="fa fa-eye me-2 text-info"></i> View Details
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/cims/docmanager/{{ $doc->id }}/download">
                                <i class="fa fa-download me-2 text-success"></i> Download
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/cims/docmanager/{{ $doc->id }}/edit">
                                <i class="fa fa-edit me-2 text-warning"></i> Edit
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/cims/docmanager/{{ $doc->id }}/email">
                                <i class="fa fa-envelope me-2 text-primary"></i> Email Document
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/cims/docmanager/{{ $doc->id }}/duplicate">
                                <i class="fa fa-copy me-2 text-secondary"></i> Duplicate
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete({{ $doc->id }})">
                                <i class="fa fa-trash me-2"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Hidden delete form -->
            <form id="delete-form-{{ $doc->id }}" action="/cims/docmanager/{{ $doc->id }}" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
