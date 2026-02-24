@php
    $cardClass = 'client-card';
    if (!$client->is_active) $cardClass .= ' inactive';
@endphp
<div class="{{ $cardClass }}"
     data-company_name="{{ strtolower($client->company_name) }}"
     data-client_code="{{ strtolower($client->client_code) }}"
     data-trading_name="{{ strtolower($client->trading_name ?? '') }}"
     data-tax_number="{{ strtolower($client->tax_number ?? '') }}"
     data-vat_number="{{ strtolower($client->vat_number ?? '') }}">
    <div class="row align-items-center">
        <!-- Left: Client Info -->
        <div class="col-lg-3 col-md-4 mb-3 mb-lg-0">
            <div class="client-code">
                {{ $client->client_code }}
                @if($showStatus ?? false)
                    @if($client->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-warning">Inactive</span>
                    @endif
                @endif
            </div>
            <div class="client-name">{{ $client->company_name }}</div>
            @if($client->trading_name)
                <div class="client-trading">Trading as: {{ $client->trading_name }}</div>
            @endif
            <div class="client-date mt-2">
                <i class="fa-regular fa-calendar"></i>
                Created: {{ $client->created_at ? $client->created_at->format('d M Y') : 'N/A' }}
            </div>
        </div>

        <!-- Middle: Key Info -->
        <div class="col-lg-8 col-md-5">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="info-block">
                        <div class="info-icon tax">
                            <i class="fa-solid fa-file-invoice"></i>
                        </div>
                        <div>
                            <div class="info-label">Income Tax Number</div>
                            <div class="info-value">{{ $client->tax_number ?: 'Not Set' }}</div>
                        </div>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="info-block">
                        <div class="info-icon tax">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <div class="info-label">CIPC Reg Number</div>
                            <div class="info-value">{{ $client->company_reg_number ?: 'Not Set' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-block">
                        <div class="info-icon director">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <div class="info-label">Payroll Number</div>
                            <div class="info-value">{{ $client->paye_number ?: 'Not Set' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-block">
                        <div class="info-icon contact">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <div class="info-label">Contact</div>
                            <div class="info-value">{{ $client->phone_mobile ?: $client->phone_mobile ?: 'No Phone' }}</div>
                            {{-- @if($client->email)
                                <div class="info-sub">{{ $client->email }}</div>
                            @endif --}}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-block">
                        <div class="info-icon vat">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <div class="info-label">VAT Number</div>
                            <div class="info-value">{{ $client->vat_number ?: 'Not Set' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-block">
                        <div class="info-icon director">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $client->email_admin ?: $client->email_admin ?: 'No Email' }}</div>
                            {{-- @if($client->email)
                                <div class="info-sub">{{ $client->email }}</div>
                            @endif --}}
                        </div>
                    </div>
                </div>
               
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="col-lg-1 col-md-3">
            <div class="d-flex justify-content-end align-items-center gap-2">
                {{-- <a href="{{route('client.show', $client->client_id)}}" class="btn btn-sm btn-outline-info" title="View">
                    <i class="fa-solid fa-eye"></i>
                </a> --}}
                <div class="dropdown action-dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{route('client.show', $client->client_id)}}"><i class="fa-solid fa-eye me-2 text-info"></i>View Details</a></li>
                        <li><a class="dropdown-item" href="{{route('client.edit', $client->client_id)}}"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @if($client->is_active)
                            <li>
                                <a class="dropdown-item text-warning" href="javascript:void(0)" onclick="confirmDeactivate({{ $client->client_id }})">
                                    <i class="fa-solid fa-ban me-2"></i>Deactivate
                                </a>
                            </li>
                        @else
                            <li>
                                <a class="dropdown-item text-success" href="javascript:void(0)" onclick="confirmActivate({{ $client->client_id }})">
                                    <i class="fa-solid fa-check-circle me-2"></i>Activate
                                </a>
                            </li>
                        @endif
                        <li><a class="dropdown-item" href="{{route('client.duplicate', $client->client_id)}}"><i class="fa-solid fa-copy me-2 text-secondary"></i>Duplicate</a></li>
                        <li><a class="dropdown-item" href="{{route('client.audit', $client->client_id)}}"><i class="fa-solid fa-history me-2 text-secondary"></i>Audit History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDelete({{ $client->client_id }})">
                                <i class="fa-solid fa-trash me-2"></i>Delete
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Delete Form -->
            <form id="delete-form-{{ $client->client_id }}" action="{{route('client.delete', $client->client_id)}}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>

            <!-- Activate Form -->
            <form id="activate-form-{{ $client->client_id }}" action="{{route('client.activate', $client->client_id)}}" method="POST" style="display: none;">
                @csrf
                @method('PUT')
            </form>

            <!-- Deactivate Form -->
            <form id="deactivate-form-{{ $client->client_id }}" action="{{route('client.deactivate', $client->client_id)}}" method="POST" style="display: none;">
                @csrf
                @method('PUT')
            </form>
        </div>
    </div>
</div>
