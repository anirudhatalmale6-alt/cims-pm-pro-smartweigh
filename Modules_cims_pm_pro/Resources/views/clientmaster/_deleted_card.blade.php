<div class="client-card deleted-card">
    <div class="row align-items-center">
        <!-- Left: Client Info -->
        <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
            <div class="client-code">
                {{ $client->client_code }}
                <span class="badge bg-danger">Deleted</span>
            </div>
            <div class="client-name">{{ $client->company_name }}</div>
            @if($client->trading_name)
                <div class="client-trading">Trading as: {{ $client->trading_name }}</div>
            @endif
            <div class="client-date mt-2">
                <i class="fa-regular fa-trash-can"></i>
                Deleted: {{ $client->deleted_at ? $client->deleted_at->format('d M Y H:i') : 'N/A' }}
            </div>
        </div>

        <!-- Middle: Info -->
        <div class="col-lg-4 col-md-4">
            <div class="info-block">
                <div class="info-icon tax">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <div class="info-label">Tax Number</div>
                    <div class="info-value">{{ $client->tax_number ?: 'Not Set' }}</div>
                </div>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="col-lg-4 col-md-4">
            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-restore" onclick="confirmRestore({{ $client->client_id }})">
                    <i class="fa-solid fa-rotate-left me-1"></i> Restore
                </button>
                <button type="button" class="btn btn-delete-forever" onclick="confirmPermanentDelete({{ $client->client_id }})">
                    <i class="fa-solid fa-skull-crossbones me-1"></i> Delete Forever
                </button>
            </div>

            <!-- Restore Form -->
            <form id="restore-form-{{ $client->client_id }}" action="{{route('client.restore', $client->client_id)}}" method="POST" style="display: none;">
                @csrf
                @method('PUT')
            </form>

            <!-- Force Delete Form -->
            <form id="force-delete-form-{{ $client->client_id }}" action="{{ route('client.delete', ['id' => $client->client_id, 'permanent' => 1]) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
