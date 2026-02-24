<?php

namespace Modules\cims_pm_pro\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\cims_pm_pro\Models\Address;
use Modules\cims_pm_pro\Models\CimsAddressType;
use Modules\cims_pm_pro\Models\CimsBankAccountType;
use Modules\cims_pm_pro\Models\CimsDirectorStatus;
use Modules\cims_pm_pro\Models\CimsDirectorType;
use Modules\cims_pm_pro\Models\ClientMaster;
use Modules\cims_pm_pro\Models\ClientMasterAddress;
use Modules\cims_pm_pro\Models\ClientMasterAudit;
use Modules\cims_pm_pro\Models\ClientMasterBank;
use Modules\cims_pm_pro\Models\ClientMasterDirector;
use Modules\cims_pm_pro\Models\ClientMasterDocument;
use Modules\cims_pm_pro\Models\ClientMasterLookup;
use Modules\cims_pm_pro\Models\ClientPosition;
use Modules\cims_pm_pro\Models\ClientTitle;
use Modules\cims_pm_pro\Models\CompanyType;
use Modules\cims_pm_pro\Models\Document;
use Modules\cims_pm_pro\Models\DocumentType;
use Modules\cims_pm_pro\Models\Person;
use Modules\cims_pm_pro\Models\RefBank;
use Modules\cims_pm_pro\Models\ShareType;
use Modules\cims_pm_pro\Models\CimsVatCycle;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Validator;

class ClientMasterController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Display listing of clients
     */
    public function index()
    {
        $clients = ClientMaster::with(['addresses'])
            ->orderBy('company_name', 'asc')
            ->get();

        $deletedClients = ClientMaster::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total' => ClientMaster::count() + ClientMaster::onlyTrashed()->count(),
            'active' => ClientMaster::where('is_active', true)->count(),
            'inactive' => ClientMaster::where('is_active', false)->count(),
            'deleted' => ClientMaster::onlyTrashed()->count(),
        ];

        return view('cims_pm_pro::clientmaster.index', compact('clients', 'deletedClients', 'stats'));
    }

    /**
     * Show form for creating a new client
     */
    public function create()
    {
        $lookups = $this->getLookups();
        $clientCode = ClientMaster::generateClientCode();
        // $addresses = $this->getActiveAddresses();

        // Fetch Cims Addresses
        $addresses = Address::active()->latest()->get()->map(function ($addr) {
            return [
                'value' => $addr->id,
                'label' => $addr->long_address,
                'description' => $addr->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        // Fetch Cims Address Types
        $address_types = CimsAddressType::active()->latest()->get()->map(function ($addr_type) {
            return [
                'value' => $addr_type->id,
                'label' => $addr_type->name,
                'description' => $addr_type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $share_types = ShareType::latest()->get()->map(function ($type) {
            return [
                'value' => $type->name,
                'label' => $type->name,
                'description' => $type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $vat_cycles = CimsVatCycle::latest()->get()->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->name,
                'description' => $type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $client_titles = ClientTitle::latest()->get()->map(function ($type) {
            return [
                'value' => $type->name,
                'label' => $type->name,
                'description' => $type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $client_positions = ClientPosition::latest()->get();

        $persons = Person::where('is_active', 1)->get()->map(function ($person) {
            $person_tooltip = <<<HTML
                            <div>
                                <strong>Person Record</strong><br>
                                <strong>Name:</strong> {$person->firstname} {$person->middlename} {$person->surname}<br>
                                <strong>ID Number:</strong> {$person->identity_number}<br>
                                <strong>Email:</strong> {$person->email}<br>
                            </div>
                        HTML;
            $person_name = $person->firstname . ' ' . ($person->middlename ? $person->middlename . ' ' : '') . $person->surname;

            return [
                'value' => $person->id,
                'label' => $person_name,
                'description' => $person_tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $director_types = CimsDirectorType::where('is_active', true)->get()->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->name,
                'description' => $type->name,  // HTML tooltip content
            ];
        })->toArray();

        $director_statuses = CimsDirectorStatus::where('is_active', true)->get()->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->name,
                'description' => $type->name,  // HTML tooltip content
            ];
        })->toArray();

        $banks = RefBank::where('is_active', 1)->get()->map(function ($bank) {
            return [
                'value' => $bank->id,
                'label' => $bank->bank_name,
                'description' => $bank->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $accountTypes = CimsBankAccountType::latest()->get();

        $existingBanks = []; // Empty for new clients
        $existingAddresses = []; // Empty for new clients

        return view('cims_pm_pro::clientmaster.clientmaster_create', compact('lookups', 'clientCode', 'addresses', 'share_types', 'vat_cycles', 'client_titles', 'client_positions', 'banks', 'existingBanks', 'existingAddresses', 'accountTypes', 'address_types', 'persons', 'director_types', 'director_statuses'));
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'client_code' => 'required|string|max:50|unique:client_master,client_code',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:150',
            'cor_certificate' => 'nullable|file|mimes:pdf|max:102400',
            'income_tax_notice_registration_upload' => 'nullable|file|mimes:pdf|max:102400',
            'payroll_notice_registration_upload' => 'nullable|file|mimes:pdf|max:102400',
            'vat_registration_upload' => 'nullable|file|mimes:pdf|max:102400',
            'sars_representative_upload' => 'nullable|file|mimes:pdf|max:102400',
            'confirmation_of_banking_uplaod' => 'nullable|file|mimes:pdf|max:102400',
            // Signature
            'signature_data' => 'nullable|string',
            // Multi-address validation
            'addresses' => 'nullable|array',
            'addresses.*.address_id' => 'required|integer|exists:cims_addresses,id',
            'addresses.*.address_name' => 'nullable|string|max:255',
            'addresses.*.address_type_id' => 'required|integer|exists:cims_address_types,id',
            'addresses.*.address_type_name' => 'required|string|max:255',
            'addresses.*.is_default' => 'nullable|boolean',
            // Multi-directors validation
            'directors' => 'nullable|array',
            'directors.*.person_id' => 'required|integer|exists:cims_persons,id',
            'directors.*.director_type_id' => 'required|integer|exists:cims_director_types,id',
            'directors.*.director_type_name' => 'required|string|max:255',
            'directors.*.director_status_id' => 'required|integer|exists:cims_director_status,id',
            'directors.*.director_status_name' => 'required|string|max:255',
            'directors.*.date_engaged' => 'nullable|date',
            'directors.*.date_resigned' => 'nullable|date',
            // Multi-bank validation
            'banks' => 'nullable|array',
            'banks.*.bank_id' => 'required|integer|exists:cims_bank_names,id',
            'banks.*.bank_name' => 'nullable|string|max:255',
            'banks.*.bank_account_holder' => 'required|string|max:255',
            'banks.*.bank_account_number' => 'required|string|max:50',
            'banks.*.bank_account_type_id' => 'required|integer|exists:cims_bank_account_types,id',
            'banks.*.bank_account_type_name' => 'nullable|string|max:255',
            'banks.*.bank_account_status_id' => 'required|integer|exists:cims_bank_account_status,id',
            'banks.*.bank_statement_frequency_id' => 'required|integer|exists:cims_bank_statement_frequency,id',
            'banks.*.bank_branch_name' => 'nullable|string|max:255',
            'banks.*.bank_branch_code' => 'nullable|string|max:50',
            'banks.*.bank_swift_code' => 'nullable|string|max:50',
            'banks.*.bank_account_date_opened' => 'nullable|date',
            'banks.*.confirmation_file' => 'nullable|file|mimes:pdf|max:102400',
        ], [
            'cor_certificate.mimes' => 'The COR 14.3 Certificate must be a PDF file.',
            'cor_certificate.max' => 'The COR 14.3 Certificate must not exceed 100MB.',

            'income_tax_notice_registration_upload.mimes' => 'Income Tax Notice Registration must be a PDF file.',
            'income_tax_notice_registration_upload.max' => 'Income Tax Notice Registration must not exceed 100MB.',

            'payroll_notice_registration_upload.mimes' => 'PAYROLL Notice of Registration must be a PDF file.',
            'payroll_notice_registration_upload.max' => 'PAYROLL Notice of Registration must not exceed 100MB.',

            'vat_registration_upload.mimes' => 'VAT Registration Registration must be a PDF file.',
            'vat_registration_upload.max' => 'VAT Registration must not exceed 100MB.',

            'sars_representative_upload.mimes' => 'SARS Representative must be a PDF file.',
            'sars_representative_upload.max' => 'SARS Representative must not exceed 100MB.',

            'confirmation_of_banking_uplaod.mimes' => 'Confirmation of Banking must be a PDF file.',
            'confirmation_of_banking_uplaod.max' => 'Confirmation of Banking must not exceed 100MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $client = new ClientMaster;
            $this->fillClientData($client, $request);
            $client->created_by = auth()->id();
            $client->save();

            // Link addresses
            $this->processAddresses($client, $request);

            $this->processDirectors($client, $request);

            $documentTypes = DocumentType::whereIn('doc_ref', [
                'COR 14.3',
                'ITAX REG',
                'PAYROLL REG',
                'VAT REG',
                'SARS REP',
                'BANK CONFIRM',
            ])->get()->keyBy('doc_ref');

            // Handle document uploads
            $this->handleDocumentUpload(
                $client,
                $request,
                'cor_certificate',
                $documentTypes->get('COR 14.3'), // CIPC - COR 14.3 Registration Certificate
                'cor_certificate_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'income_tax_notice_registration_upload',
                $documentTypes->get('ITAX REG'), // SARS INCOME TAX - Notice of Registration
                'income_tax_notice_registration_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'payroll_notice_registration_upload',
                $documentTypes->get('PAYROLL REG'), // Payroll Notice of Registration
                'payroll_notice_registration_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'vat_registration_upload',
                $documentTypes->get('VAT REG'), // VAT Notice Of Registration
                'vat_registration_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'sars_representative_upload',
                $documentTypes->get('SARS REP'), // SARS Representative
                'sars_representative_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'confirmation_of_banking_uplaod',
                $documentTypes->get('BANK CONFIRM'), // Confirmation of Banking
                'confirmation_of_banking_uplaoded'
            );

            // Save multi-bank records
            $this->processBanks($client, $request);

            // Audit log
            $this->logAudit($client->client_id, 'created', null, $client->toArray());

            DB::commit();

            return redirect()->route('client.index')
                ->with('success', 'Client "'.$client->company_name.'" created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error creating client: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified client
     */
    public function show($id)
    {
        $client = ClientMaster::with(['addresses', 'audits.user'])
            ->findOrFail($id);

        $lookups = $this->getLookups();

        return view('cims_pm_pro::clientmaster.show', compact('client', 'lookups'));
    }

    /**
     * Show form for editing the specified client
     */
    public function edit($id)
    {
        $client = ClientMaster::with(['addresses', 'documents'])->findOrFail($id);

        $lookups = $this->getLookups();

        $addresses = Address::active()->latest()->get()->map(function ($addr) {
            return [
                'value' => $addr->id,
                'label' => $addr->long_address,
                'description' => $addr->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $persons = Person::where('is_active', 1)->get()->map(function ($person) {
            $person_tooltip = <<<HTML
                            <div>
                                <strong>Person Record</strong><br>
                                <strong>Name:</strong> {$person->firstname} {$person->middlename} {$person->surname}<br>
                                <strong>ID Number:</strong> {$person->identity_number}<br>
                                <strong>Email:</strong> {$person->email}<br>
                            </div>
                        HTML;

            $person_name = $person->firstname . ' ' . ($person->middlename ? $person->middlename . ' ' : '') . $person->surname . ' ' . "({$person->identity_number})";

            return [
                'value' => $person->id,
                'label' => $person_name,
                'description' => $person_tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $director_types = CimsDirectorType::where('is_active', true)->get()->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->name,
                'description' => $type->name,  // HTML tooltip content
            ];
        })->toArray();

        $director_statuses = CimsDirectorStatus::where('is_active', true)->get()->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->name,
                'description' => $type->name,  // HTML tooltip content
            ];
        })->toArray();

        // Fetch Cims Address Types
        $address_types = CimsAddressType::active()->latest()->get()->map(function ($addr_type) {
            return [
                'value' => $addr_type->id,
                'label' => $addr_type->name,
                'description' => $addr_type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $share_types = ShareType::latest()->get()->map(function ($type) {
            return [
                'value' => $type->name,
                'label' => $type->name,
                'description' => $type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $vat_cycles = CimsVatCycle::latest()->get()->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->name,
                'description' => $type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $client_titles = ClientTitle::latest()->get()->map(function ($type) {
            return [
                'value' => $type->name,
                'label' => $type->name,
                'description' => $type->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        $client_positions = ClientPosition::latest()->get();

        $banks = RefBank::where('is_active', 1)->get()->map(function ($bank) {
            return [
                'value' => $bank->id,
                'label' => $bank->bank_name,
                'description' => $bank->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        // Load existing client banks
        $existingBanks = $client->bankAccounts()->get()->map(function ($bank) {
            $document = $bank->document()->first();

            // $file_path = asset('storage/'.$document->file_path);
            return [
                'db_id' => $bank->id,
                'bank_id' => $bank->bank_id,
                'bank_name' => $bank->bank_name,
                'bank_account_holder' => $bank->bank_account_holder,
                'bank_account_number' => $bank->bank_account_number,
                'bank_account_type_id' => $bank->bank_account_type_id,
                'bank_account_type_name' => $bank->bank_account_type_name,
                'bank_account_status_id' => $bank->bank_account_status_id,
                'bank_account_status_name' => $bank->bank_account_status_name,
                'bank_branch_name' => $bank->bank_branch_name,
                'bank_branch_code' => $bank->bank_branch_code,
                'bank_swift_code' => $bank->bank_swift_code,
                'bank_logo' => $bank->bank?->bank_logo,
                'bank_account_date_opened' => $bank->bank_account_date_opened?->format('Y-m-d'),
                'is_default' => $bank->is_default ? true : false,
                'document' => $document->id,
            ];
        })->toArray();

        // Load existing client addresses
        $existingAddresses = $client->addresses()->get()->map(function ($address) {
            return [
                'db_id' => $address->id,
                'address_id' => $address->address_id,
                'address_name' => $address->address?->long_address,
                'address_type_id' => $address->address_type_id,
                'address_type_name' => $address->address_type_name,
                'is_default' => $address->is_default ? true : false,
            ];
        })->toArray();


        $existingDirectors = $client->directors()->get()->map(function ($director) {
            $profile_photo = asset("storage/$director->profile_photo") ?: asset('smartdash/images/user.jpg');
            $address = $director->address_line . ', ' . $director->suburb . ', ' . $director->city . ', ' . $director->province . ', ' . $director->address_country;
            return [
                'db_id' => $director->id,
                'person_id' => $director->person_id,
                'person_name' => $director->firstname . ' ' . ($director->middlename ? $director->middlename . ' ' : '') . $director->surname,
                'identity_number' => $director->identity_number,
                'address' => $address,
                'director_type_id' => $director->director_type_id,
                'director_type_name' => $director->director_type_name,
                'director_status_id' => $director->director_status_id,
                'director_status_name' => $director->director_status_name,
                'date_engaged' => $director->date_engaged,
                'date_resigned' => $director->date_resigned,
                'number_of_director_shares' => $director->number_of_director_shares,
                'director_profile_image' => $profile_photo,
                'identity_type' => $director->identity_type,
                'nationality' => $director->nationality,

            ];
        })->toArray();

        return view('cims_pm_pro::clientmaster.clientmaster_create', compact('client', 'lookups', 'addresses', 'share_types', 'vat_cycles', 'client_titles', 'client_positions', 'banks', 'existingBanks', 'existingAddresses', 'address_types', 'persons', 'director_types', 'director_statuses', 'existingDirectors'));
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, $id)
    {
        $client = ClientMaster::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'client_code' => 'required|string|max:50|unique:client_master,client_code,'.$id.',client_id',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:150',
            'cor_certificate' => 'nullable|file|mimes:pdf|max:102400',
            'income_tax_notice_registration_upload' => 'nullable|file|mimes:pdf|max:102400',
            'payroll_notice_registration_upload' => 'nullable|file|mimes:pdf|max:102400',
            'vat_registration_upload' => 'nullable|file|mimes:pdf|max:102400',
            'sars_representative_upload' => 'nullable|file|mimes:pdf|max:102400',
            'confirmation_of_banking_uplaod' => 'nullable|file|mimes:pdf|max:102400',
            // Signature
            'signature_data' => 'nullable|string',
            // Multi-address validation
            'addresses' => 'nullable|array',
            'addresses.*.address_id' => 'required|integer|exists:cims_addresses,id',
            'addresses.*.address_name' => 'nullable|string|max:255',
            'addresses.*.address_type_id' => 'required|integer|exists:cims_address_types,id',
            'addresses.*.address_type_name' => 'required|string|max:255',
            'addresses.*.is_default' => 'nullable|boolean',
            // Existing addresses — only update default flag
            'existing_addresses' => 'nullable|array',
            'existing_addresses.*.id' => 'required|integer|exists:client_master_addresses,id',
            'existing_addresses.*.is_default' => 'nullable|boolean',
            // Multi-directors validation
            'directors' => 'nullable|array',
            'directors.*.person_id' => 'required|integer|exists:cims_persons,id',
            'directors.*.director_type_id' => 'required|integer|exists:cims_director_types,id',
            'directors.*.director_type_name' => 'required|string|max:255',
            'directors.*.director_status_id' => 'required|integer|exists:cims_director_status,id',
            'directors.*.director_status_name' => 'required|string|max:255',
            'directors.*.date_engaged' => 'nullable|date',
            'directors.*.date_resigned' => 'nullable|date',
            // Deleted addresses
            'deleted_address_ids' => 'nullable|array',
            'deleted_address_ids.*' => 'nullable|integer|exists:client_master_addresses,id',
            // Multi-bank validation
            'banks' => 'nullable|array',
            'banks.*.bank_id' => 'required|integer|exists:cims_bank_names,id',
            'banks.*.bank_name' => 'nullable|string|max:255',
            'banks.*.bank_account_holder' => 'required|string|max:255',
            'banks.*.bank_account_number' => 'required|string|max:50',
            'banks.*.bank_account_type_id' => 'required|integer|exists:cims_bank_account_types,id',
            'banks.*.bank_account_type_name' => 'nullable|string|max:255',
            'banks.*.bank_account_status_id' => 'required|integer|exists:cims_bank_account_status,id',
            'banks.*.bank_statement_frequency_id' => 'required|integer|exists:cims_bank_statement_frequency,id',
            'banks.*.bank_branch_name' => 'nullable|string|max:255',
            'banks.*.bank_branch_code' => 'nullable|string|max:50',
            'banks.*.bank_swift_code' => 'nullable|string|max:50',
            'banks.*.bank_account_date_opened' => 'nullable|date',
            'banks.*.confirmation_file' => 'nullable|file|mimes:pdf|max:102400',
            // Existing banks — only update is_checked status
            'existing_banks' => 'nullable|array',
            'existing_banks.*.id' => 'required|integer|exists:client_master_banks,id',
            'existing_banks.*.is_default' => 'required|boolean',
            // Deleted banks
            'deleted_bank_ids' => 'nullable|array',
            'deleted_bank_ids.*' => 'nullable|integer|exists:client_master_banks,id',
        ], [
            'cor_certificate.mimes' => 'The COR 14.3 Certificate must be a PDF file.',
            'cor_certificate.max' => 'The COR 14.3 Certificate must not exceed 10MB.',

            'income_tax_notice_registration_upload.mimes' => 'Income Tax Notice Registration must be a PDF file.',
            'income_tax_notice_registration_upload.max' => 'Income Tax Notice Registration must not exceed 10MB.',

            'payroll_notice_registration_upload.mimes' => 'PAYROLL Notice of Registration must be a PDF file.',
            'payroll_notice_registration_upload.max' => 'PAYROLL Notice of Registration must not exceed 10MB.',

            'vat_registration_upload.mimes' => 'VAT Registration Registration must be a PDF file.',
            'vat_registration_upload.max' => 'VAT Registration must not exceed 10MB.',

            'sars_representative_upload.mimes' => 'SARS Representative must be a PDF file.',
            'sars_representative_upload.max' => 'SARS Representative must not exceed 10MB.',

            'confirmation_of_banking_uplaod.mimes' => 'Confirmation of Banking must be a PDF file.',
            'confirmation_of_banking_uplaod.max' => 'Confirmation of Banking must not exceed 10MB.',

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $oldValues = $client->toArray();

            $this->fillClientData($client, $request);
            $client->updated_by = auth()->id();
            $client->save();

            $documentTypes = DocumentType::whereIn('doc_ref', [
                'COR 14.3',
                'ITAX REG',
                'PAYROLL REG',
                'VAT REG',
                'SARS REP',
                'BANK CONFIRM',
            ])->get()->keyBy('doc_ref');

            // Handle document uploads
            $this->handleDocumentUpload(
                $client,
                $request,
                'cor_certificate',
                $documentTypes->get('COR 14.3'),
                'cor_certificate_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'income_tax_notice_registration_upload',
                $documentTypes->get('ITAX REG'),
                'income_tax_notice_registration_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'payroll_notice_registration_upload',
                $documentTypes->get('PAYROLL REG'),
                'payroll_notice_registration_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'vat_registration_upload',
                $documentTypes->get('VAT REG'),
                'vat_registration_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'sars_representative_upload',
                $documentTypes->get('SARS REP'),
                'sars_representative_uploaded'
            );

            $this->handleDocumentUpload(
                $client,
                $request,
                'confirmation_of_banking_uplaod',
                $documentTypes->get('BANK CONFIRM'),
                'confirmation_of_banking_uplaoded'
            );

            // Sync multi-bank records
            $this->processBanks($client, $request);

            // Sync multi-address records
            $this->processAddresses($client, $request);

            
            $this->processDirectors($client, $request);

            // Audit log
            $this->logAudit($client->client_id, 'updated', $oldValues, $client->toArray());

            DB::commit();

            return redirect()->route('client.index')
                ->with('success', 'Client "'.$client->company_name.'" updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error updating client: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Soft delete the specified client
     */
    public function destroy(Request $request, $id)
    {
        // Permanent delete (from trashed view)
        if ($request->get('permanent')) {
            $client = ClientMaster::withTrashed()->findOrFail($id);
            $this->logAudit($id, 'permanently_deleted', $client->toArray(), null);
            $client->forceDelete();

            return redirect()->route('client.index')
                ->with('success', 'Client permanently deleted.');
        }

        // Soft delete
        $client = ClientMaster::findOrFail($id);
        $this->logAudit($id, 'deleted', $client->toArray(), null);
        $client->delete();

        return redirect()->route('client.index')
            ->with('success', 'Client "'.$client->company_name.'" moved to trash.');
    }

    /**
     * Restore a soft-deleted client
     */
    public function restore($id)
    {
        $client = ClientMaster::withTrashed()->findOrFail($id);
        $client->restore();
        $this->logAudit($id, 'restored', null, $client->toArray());

        return redirect()->route('client.index')
            ->with('success', 'Client "'.$client->company_name.'" restored successfully.');
    }

    /**
     * Activate a client
     */
    public function activate($id)
    {
        $client = ClientMaster::findOrFail($id);
        $oldData = $client->toArray();
        $client->is_active = true;
        $client->save();
        $this->logAudit($id, 'activated', $oldData, $client->toArray());

        return redirect()->route('client.index')
            ->with('success', 'Client "'.$client->company_name.'" activated successfully.');
    }

    /**
     * Deactivate a client
     */
    public function deactivate($id)
    {
        $client = ClientMaster::findOrFail($id);
        $oldData = $client->toArray();
        $client->is_active = false;
        $client->save();
        $this->logAudit($id, 'deactivated', $oldData, $client->toArray());

        return redirect()->route('client.index')
            ->with('success', 'Client "'.$client->company_name.'" deactivated successfully.');
    }

    /**
     * Duplicate a client
     */
    public function duplicate($id)
    {
        $original = ClientMaster::with('addresses')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Create new client with copied data
            $newClient = $original->replicate();
            $newClient->client_code = ClientMaster::generateClientCode();
            $newClient->company_name = $original->company_name.' (Copy)';
            $newClient->created_by = auth()->id();
            $newClient->created_at = now();
            $newClient->updated_at = now();
            $newClient->save();

            // Copy address links
            foreach ($original->addresses as $addr) {
                ClientMasterAddress::create([
                    'client_id' => $newClient->client_id,
                    'address_id' => $addr->address_id,
                    'address_type' => $addr->address_type,
                    'is_checked' => $addr->is_checked,
                    'is_default' => $addr->is_default,
                ]);
            }

            // Audit log
            $this->logAudit($newClient->client_id, 'created_from_duplicate', ['original_id' => $id], $newClient->toArray());

            DB::commit();

            return redirect()->route('client.edit', $newClient->client_id)
                ->with('success', 'Client duplicated successfully. You can now edit the copy.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error duplicating client: '.$e->getMessage());
        }
    }

    /**
     * Show audit history for a client
     */
    public function audit($id)
    {
        $client = ClientMaster::withTrashed()->findOrFail($id);
        $audits = ClientMasterAudit::where('client_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cims_pm_pro::clientmaster.audit', compact('client', 'audits'));
    }

    /**
     * Check if restore would create a duplicate
     */
    public function checkRestore($id)
    {
        $trashedClient = ClientMaster::withTrashed()->findOrFail($id);

        // Check if a client with the same code exists and is not trashed
        $duplicate = ClientMaster::where('client_code', $trashedClient->client_code)
            ->whereNull('deleted_at')
            ->first();

        return response()->json([
            'has_duplicate' => $duplicate !== null,
            'duplicate_code' => $duplicate ? $duplicate->client_code : null,
        ]);
    }

    /**
     * Link an address to the client
     */
    public function linkAddress(Request $request, $id)
    {
        $client = ClientMaster::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'address_id' => 'required|integer',
            'address_type' => 'required|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Check if client already has this address type
        $exists = ClientMasterAddress::where('client_id', $id)
            ->where('address_type', $request->address_type)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'This client already has a '.$request->address_type.' address. Remove it first before adding a new one.',
            ], 422);
        }

        ClientMasterAddress::create([
            'client_id' => $id,
            'address_id' => $request->address_id,
            'address_type' => $request->address_type,
        ]);

        return response()->json(['success' => true, 'message' => 'Address linked successfully.']);
    }

    /**
     * Unlink an address from the client
     */
    public function unlinkAddress($clientId, $addressId)
    {
        ClientMasterAddress::where('client_id', $clientId)
            ->where('address_id', $addressId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Address unlinked.']);
    }

    /**
     * Get active addresses for AJAX dropdown
     */
    public function getAddresses(Request $request)
    {
       $addresses = Address::active()->latest()->get()->map(function ($addr) {
            return [
                'value' => $addr->id,
                'label' => $addr->long_address,
                'description' => $addr->tooltip,  // HTML tooltip content
            ];
        })->toArray();

        return response()->json($addresses);
    }

    /**
     * Check if company name exists (AJAX)
     */
    public function checkCompanyName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        if (empty($name)) {
            return response()->json(['exists' => false]);
        }

        $exists = ClientMaster::companyNameExists($name, $excludeId);
        $formatted = ClientMaster::formatCompanyName($name);

        return response()->json([
            'exists' => $exists,
            'formatted' => $formatted,
        ]);
    }

    /**
     * Generate next available client code from company name (AJAX)
     */
    public function generateCode(Request $request)
    {
        $companyName = $request->input('company_name');
        $code = ClientMaster::generateClientCode($companyName);

        return response()->json(['code' => $code]);
    }

    /**
     * Get company type by registration number code (AJAX)
     * Extracts the type code from YYYY/XXXXXX/XX format and returns the matching company type
     */
    public function getCompanyTypeByCode(Request $request)
    {
        $regNumber = $request->input('reg_number');

        if (empty($regNumber)) {
            return response()->json(['found' => false]);
        }

        // Extract the type code from the registration number (last 2 digits after final /)
        // Format: YYYY/XXXXXX/XX (e.g., 2020/123456/07)
        if (preg_match('/\/(\d{2})$/', $regNumber, $matches)) {
            $typeCode = $matches[1];
            $companyType = CompanyType::getByCode($typeCode);

            if ($companyType) {
                return response()->json([
                    'found' => true,
                    'type_code' => $typeCode,
                    'type_name' => $companyType->type_name,
                    'type_abbreviation' => $companyType->type_abbreviation,
                    'type_label' => $companyType->type_label,
                    'type_id' => $companyType->id,
                ]);
            }
        }

        return response()->json(['found' => false]);
    }

    /**
     * Get address details by ID (AJAX)
     */
    public function get_address($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return response()->json([
            'address' => $address,
        ]);
    }

    /**
     * Get bank details by ID (AJAX) - returns branch info, account types, statuses, statement frequencies
     */
    public function get_bank($id)
    {
        $bank = RefBank::find($id);

        if (!$bank) {
            return response()->json(['error' => 'Bank not found'], 404);
        }

        // Get account types for this bank
        $accountTypes = CimsBankAccountType::where('bank_link_id', $id)
            ->where('is_active', 1)
            ->get();

        // Get account statuses for this bank
        $accountStatuses = \Illuminate\Support\Facades\DB::table('cims_bank_account_status')
            ->where('bank_link_id', $id)
            ->where('is_active', 1)
            ->get();

        // Get statement frequencies for this bank
        $statementFrequencies = \Illuminate\Support\Facades\DB::table('cims_bank_statement_frequency')
            ->where('bank_link_id', $id)
            ->where('is_active', 1)
            ->get();

        return response()->json([
            'branch_name' => $bank->branch_name,
            'branch_code' => $bank->branch_code,
            'swift_code' => $bank->swift_code,
            'bank_logo' => $bank->bank_logo,
            'account_types' => $accountTypes,
            'account_statuses' => $accountStatuses,
            'banke_statement_frequencies' => $statementFrequencies,
        ]);
    }

    /**
     * Update an existing director (AJAX) - edit form only
     */
    public function updateDirector(Request $request, int $directorId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer|exists:client_master,client_id',
            'director_type_id' => 'required|integer|exists:cims_director_types,id',
            'director_type_name' => 'required|string|max:255',
            'director_status_id' => 'required|integer|exists:cims_director_status,id',
            'director_status_name' => 'required|string|max:255',
            'date_engaged' => 'required|date_format:Y-m-d',
            'date_resigned' => 'nullable|date_format:Y-m-d|after_or_equal:date_engaged',
        ], [
            'date_resigned.after_or_equal' => 'Date Resigned must be on or after Date Engaged.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $clientId = (int) $request->input('client_id');

        $director = ClientMasterDirector::query()
            ->where('id', $directorId)
            ->where('client_id', $clientId)
            ->firstOrFail();

        $director->director_type_id = (int) $request->input('director_type_id');
        $director->director_type_name = (string) $request->input('director_type_name');
        $director->director_status_id = (int) $request->input('director_status_id');
        $director->director_status_name = (string) $request->input('director_status_name');
        $director->date_engaged = (string) $request->input('date_engaged');
        $director->number_of_director_shares = (string) $this->normalizeIntegerInput($request->input('number_of_director_shares')) ?: null;
        $director->date_resigned = (string) $request->input('date_resigned') ?: null;
        $director->save();

        $dateEngaged = $director->date_engaged;
        try {
            $dateEngaged = $dateEngaged ? Carbon::parse($dateEngaged)->format('Y-m-d') : null;
        } catch (\Exception $e) {
        }

        $dateResigned = $director->date_resigned;
        try {
            $dateResigned = $dateResigned ? Carbon::parse($dateResigned)->format('Y-m-d') : null;
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'director' => [
                'db_id' => $director->id,
                'person_id' => $director->person_id,
                'person_name' => $director->surname ?: trim(($director->firstname ?? '').' '.($director->surname ?? '')),
                'director_type_id' => $director->director_type_id,
                'director_type_name' => $director->director_type_name,
                'director_status_id' => $director->director_status_id,
                'director_status_name' => $director->director_status_name,
                'number_of_director_shares' => $director->number_of_director_shares,
                'date_engaged' => $dateEngaged,
                'date_resigned' => $dateResigned,
            ],
        ]);
    }

    /**
     * Fill client data from request
     */
    private function fillClientData(ClientMaster $client, Request $request)
    {
        // Company Information
        $client->company_name = $request->company_name;
        $client->sign_text = $request->signature_data;
        $client->company_reg_number = $request->company_reg_number;
        $client->company_type = $request->company_type;
        $client->bizportal_number = $request->bizportal_number;
        $client->company_reg_date = $this->convertDate($request->company_reg_date);
        $client->trading_name = $request->trading_name;
        $client->financial_year_end = $request->financial_year_end;
        $client->month_no = $request->month_no;
        $client->client_code = $request->client_code;
        $client->number_of_directors = $request->number_of_directors;

        $numberOfShares = $request->number_of_shares;
        if ($numberOfShares) { // Thousand Separator Problem
            // Remove spaces and any non-numeric characters except decimal point
            $numberOfShares = preg_replace('/[^\d.]/', '', $numberOfShares);
            // Convert to integer (truncate decimals if present)
            $numberOfShares = (int) $numberOfShares;
        }
        $client->number_of_shares = $numberOfShares;

        $client->share_type_name = $request->share_type;

        // Income Tax Registration
        $client->tax_number = $request->tax_number;
        $client->tax_reg_date = $this->convertDate($request->tax_reg_date);
        $client->cipc_annual_returns = $request->cipc_annual_returns;

        // Payroll Registration
        $client->paye_number = $request->paye_number;
        $client->sdl_number = $request->sdl_number;
        $client->uif_number = $request->uif_number;
        $client->dept_labour_number = $request->dept_labour_number;
        $client->wca_coida_number = $request->wca_coida_number;
        $client->payroll_liability_date = $this->convertDate($request->payroll_liability_date);

        // VAT Registration
        $client->vat_number = $request->vat_number;
        $client->vat_reg_date = $this->convertDate($request->vat_reg_date);
        $client->vat_return_cycle = $request->vat_return_cycle;
        $client->vat_cycle_id = $request->vat_cycle;
        $vatCycleName = '';
        if (! empty($request->vat_cycle)) {
            $vatCycleName = CimsVatCycle::find($request->vat_cycle)?->name ?? '';
        }
        $client->vat_cycle_name = $vatCycleName;
        $client->vat_effect_from = $this->convertDate($request->vat_effect_from);

        // Contact Information
        $client->phone_business = $request->phone_business;
        $client->phone_mobile = $request->phone_mobile;
        $client->phone_whatsapp = $request->phone_whatsapp;
        $client->email = $request->email_compliance;
        $client->email_admin = $request->email_admin;
        $client->direct = $request->direct;
        $client->website = $request->website;

        // SARS Representative
        $client->sars_rep_first_name = $request->sars_rep_first_name;
        $client->sars_rep_middle_name = $request->sars_rep_middle_name;
        $client->sars_rep_surname = $request->sars_rep_surname;
        $client->sars_rep_initial = $request->sars_rep_initial;
        $client->sars_rep_title = $request->sars_rep_title;
        $client->sars_rep_gender = $request->sars_rep_gender;
        $client->sars_rep_id_number = $request->sars_rep_id_number;
        $client->sars_rep_id_type = $request->sars_rep_id_type;
        $client->sars_rep_id_issue_date = $this->convertDate($request->sars_rep_id_issue_date);
        $client->sars_rep_tax_number = $request->sars_rep_tax_number;
        $client->sars_rep_position = $request->sars_rep_position;
        $client->sars_rep_date_registered = $this->convertDate($request->sars_rep_date_registered);

        // SARS E-Filing
        $client->sars_login = $request->sars_login;
        $client->sars_password = $request->sars_password;
        $client->sars_otp_mobile = $request->sars_otp_mobile;
        $client->sars_otp_email = $request->sars_otp_email;

        // Banking Details
        $client->bank_account_holder = $request->bank_account_holder;
        $client->bank_account_number = $request->bank_account_number;
        $client->bank_account_type = $request->bank_account_type;
        $client->bank_name = $request->bank_name;
        $client->bank_branch_code = $request->bank_branch_code;

        // Director Details
        $client->director_first_name = $request->director_first_name;
        $client->director_middle_name = $request->director_middle_name;
        $client->director_surname = $request->director_surname;
        $client->director_initial = $request->director_initial;
        $client->director_title = $request->director_title;
        $client->director_gender = $request->director_gender;
        $client->director_id_number = $request->director_id_number;
        $client->director_id_type = $request->director_id_type;
        $client->director_id_issue_date = $this->convertDate($request->director_id_issue_date);
        $client->director_marital_status = $request->director_marital_status;
        $client->director_marriage_type = $request->director_marriage_type;
        $client->director_marriage_date = $this->convertDate($request->director_marriage_date);

        // Partner Details
        $client->partner_first_name = $request->partner_first_name;
        $client->partner_middle_name = $request->partner_middle_name;
        $client->partner_surname = $request->partner_surname;
        $client->partner_title = $request->partner_title;
        $client->partner_gender = $request->partner_gender;
        $client->partner_id_number = $request->partner_id_number;
        $client->partner_id_type = $request->partner_id_type;
        $client->partner_id_issue_date = $this->convertDate($request->partner_id_issue_date);

        // Status
        $client->is_active = $request->has('is_active') ? 1 : 0;
    }

    /**
     * Convert date string to MySQL format (Y-m-d)
     */
    private function convertDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    private function normalizeIntegerInput(null|string|int|float $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d-]/u', '', (string) $value);

        if ($normalized === '' || $normalized === '-') {
            return null;
        }

        return (int) $normalized;
    }

    private function processDirectors(ClientMaster $client, Request $request): void
    {
        // Update existing addresses — only default flag
        // if ($request->has('existing_directors')) {
        //     foreach ($request->input('existing_directors', []) as $existingDirector) {
        //         $client->directors()
        //             ->where('id', $existingDirector['id'])
        //             ->update([
        //                'director_type_id' => $existingDirector['director_type_id'],
        //                 'director_type_name' => $existingDirector['director_type_name'],
        //                 'director_status_id' => $existingDirector['director_status_id'],
        //                 'director_status_name' => $existingDirector['director_status_name'],
        //                 'date_engaged' => $existingDirector['date_engaged'],
        //                 'number_of_director_shares' =>  $existingDirector['director_type_id'] == '1' ? $existingDirector['number_of_director_shares']  : 0,
        //                 'date_resigned' => $existingDirector['date_resigned']  ?: null
        //             ]);
        //     }
        // }

        // Create new address records with full duplication
        foreach ($request->input('directors', []) as $directorData) {
            $director = Person::find($directorData['person_id']);

            if ($director) {
                ClientMasterDirector::create([
                    'client_id' => $client->client_id,
                    'person_id' => $director->id,
                    'number_of_director_shares' => $directorData['director_type_id'] == '1' ? $this->normalizeIntegerInput($directorData['number_of_director_shares']) : 0,
                    'director_type_id' => $directorData['director_type_id'],
                    'director_type_name' => $directorData['director_type_name'],
                    'director_status_id' => $directorData['director_status_id'],
                    'director_status_name' => $directorData['director_status_name'],
                    'date_engaged' => $directorData['date_engaged'],
                    'date_resigned' => $directorData['date_resigned'] ?: null,
                    'citizenship' => $director->citizenship ?? 'SOUTH AFRICAN',
                    'identity_type' => $director->identity_type ?? null,
                    'identity_number' => $director->identity_number ?? null,
                    'gender' => $director->gender ?? null,
                    'date_of_birth' => $director->date_of_birth ?? null,
                    'date_of_issue' => $director->date_of_issue ?? null,
                    'person_status' => $director->person_status ?? 'Alive',
                    'date_of_death' => $director->date_of_death ?? null,
                    'ethnic_group' => $director->ethnic_group ?? null,
                    'disability' => $director->disability ?? '0',
                    'passport_number' => $director->passport_number ?? null,
                    'passport_expiry' => $director->passport_expiry ?? null,
                    'country' => $director->country ?? 'South Africa',
                    'country_code' => $director->country_code ?? null,
                    'nationality' => $director->nationality ?? 'South African',
                    'title' => $director->title ?? null,
                    'initials' => $director->initials ?? null,
                    'surname' => $director->surname ?? '',
                    'firstname' => $director->firstname ?? '',
                    'middlename' => $director->middlename ?? null,
                    'known_as' => $director->known_as ?? null,
                    'tax_number' => $director->tax_number ?? null,
                    'mobile_phone' => $director->mobile_phone ?? null,
                    'whatsapp_number' => $director->whatsapp_number ?? null,
                    'office_phone' => $director->office_phone ?? null,
                    'other_phone' => $director->other_phone ?? null,
                    'email' => $director->email ?? null,
                    'accounts_email' => $director->accounts_email ?? null,
                    'marital_status' => $director->marital_status ?? null,
                    'marital_status_date' => $director->marital_status_date ?? null,
                    // Spouse fields
                    'sp_citizenship' => $director->sp_citizenship ?? null,
                    'sp_identity_type' => $director->sp_identity_type ?? null,
                    'sp_identity_number' => isset($director->sp_identity_number) ? str_replace(' ', '', $director->sp_identity_number) : null,
                    'sp_date_of_birth' => $director->sp_date_of_birth ?? null,
                    'sp_date_of_issue' => $director->sp_date_of_issue ?? null,
                    'sp_person_status' => $director->sp_person_status ?? null,
                    'sp_gender' => $director->sp_gender ?? null,
                    'sp_ethnic_group' => $director->sp_ethnic_group ?? null,
                    'sp_disability' => $director->sp_disability ?? null,
                    'sp_title' => $director->sp_title ?? null,
                    'sp_initials' => $director->sp_initials ?? null,
                    'sp_tax_number' => $director->sp_tax_number ?? null,
                    'sp_firstname' => $director->sp_firstname ?? null,
                    'sp_middlename' => $director->sp_middlename ?? null,
                    'sp_surname' => $director->sp_surname ?? null,
                    'sp_known_as' => $director->sp_known_as ?? null,
                    'sp_mobile_phone' => $director->sp_mobile_phone ?? null,
                    'sp_whatsapp_number' => $director->sp_whatsapp_number ?? null,
                    'sp_office_phone' => $director->sp_office_phone ?? null,
                    'sp_other_phone' => $director->sp_other_phone ?? null,
                    'sp_email' => $director->sp_email ?? null,
                    'sp_accounts_email' => $director->sp_accounts_email ?? null,
                    // Address fields
                    'complex_name' => $director->complex_name ?? null,
                    'address_line' => $director->address_line ?? null,
                    'address_line_2' => $director->address_line_2 ?? null,
                    'suburb' => $director->suburb ?? null,
                    'city' => $director->city ?? null,
                    'postal_code' => $director->postal_code ?? null,
                    'province' => $director->province ?? null,
                    'address_country' => $director->address_country ?? 'South Africa',
                    'latitude' => $director->latitude ?? null,
                    'longitude' => $director->longitude ?? null,
                    // Banking fields
                    'bank_account_holder' => $director->bank_account_holder ?? null,
                    'bank_name' => $director->bank_name ?? null,
                    'bank_branch' => $director->bank_branch ?? null,
                    'bank_account_number' => isset($director->bank_account_number) ? str_replace(' ', '', $director->bank_account_number) : null,
                    'bank_account_type' => $director->bank_account_type ?? null,
                    'bank_swift_code' => $director->bank_swift_code ?? null,
                    'bank_account_status' => $director->bank_account_status ?? null,
                    'bank_date_opened' => $director->bank_date_opened ?? null,
                    'notes' => $director->notes ?? null,
                    'profile_photo' => $director->profile_photo ?? null,
                    'id_front_image' => $director->id_front_image ?? null,
                    'id_back_image' => $director->id_back_image ?? null,
                    'green_book_image' => $director->green_book_image ?? null,
                    'update_image' => $director->update_image ?? null,
                    'passport_image' => $director->passport_image ?? null,
                    'poa_image' => $director->poa_image ?? null,
                    'banking_image' => $director->banking_image ?? null,
                    // Signature (base64 data URL stored directly or saved as file)
                    'signature_image' => $director->signature_data ?? null,
                    'is_active' => 1,
                    'created_by' => 'System',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Soft-delete specified addresses
        // $deletedAddressIds = $request->input('deleted_address_ids', []);
        // if (! empty($deletedAddressIds)) {
        //     ClientMasterAddress::whereIn('id', $deletedAddressIds)
        //         ->where('client_id', $client->client_id)
        //         ->delete();
        // }
    }

    private function processAddresses(ClientMaster $client, Request $request): void
    {
        // Update existing addresses — only default flag
        if ($request->has('existing_addresses')) {
            foreach ($request->input('existing_addresses', []) as $existingAddress) {
                $client->addresses()
                    ->where('id', $existingAddress['id'])
                    ->update([
                        'is_default' => filter_var($existingAddress['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ]);
            }
        }

        // Create new address records with full duplication
        foreach ($request->input('addresses', []) as $addressData) {
            $address = Address::find($addressData['address_id']);

            if ($address) {
                ClientMasterAddress::create([
                    'client_id' => $client->client_id,
                    'address_id' => $address->id,
                    'address_type_id' => $addressData['address_type_id'] ?? null,
                    'address_type_name' => $addressData['address_type_name'] ?? null,
                    'unit_number' => $address->unit_number,
                    'complex_name' => $address->complex_name,
                    'street_number' => $address->street_number,
                    'street_name' => $address->street_name,
                    'suburb' => $address->suburb,
                    'city' => $address->city,
                    'postal_code' => $address->postal_code,
                    'province' => $address->province,
                    'municipality' => $address->municipality,
                    'ward' => $address->ward,
                    'country' => $address->country,
                    'long_address' => $address->long_address,
                    'google_address' => $address->google_address,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'map_url' => $address->map_url,
                    'is_checked' => false,
                    'is_default' => filter_var($addressData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);
            }
        }

        // Soft-delete specified addresses
        $deletedAddressIds = $request->input('deleted_address_ids', []);
        if (! empty($deletedAddressIds)) {
            ClientMasterAddress::whereIn('id', $deletedAddressIds)
                ->where('client_id', $client->client_id)
                ->delete();
        }
    }

    /**
     * Get all lookup values
     */
    private function getLookups()
    {
        return [
            'titles' => ClientMasterLookup::getByCategory('title'),
            'genders' => ClientMasterLookup::getByCategory('gender'),
            'id_types' => ClientMasterLookup::getByCategory('id_type'),
            'marital_statuses' => ClientMasterLookup::getByCategory('marital_status'),
            'marriage_types' => ClientMasterLookup::getByCategory('marriage_type'),
            'provinces' => ClientMasterLookup::getByCategory('province'),
            'address_types' => ClientMasterLookup::getByCategory('address_type'),
            'vat_cycles' => ClientMasterLookup::getByCategory('vat_cycle'),
            'account_types' => ClientMasterLookup::getByCategory('account_type'),
            'banks' => ClientMasterLookup::getByCategory('bank'),
        ];
    }

    /**
     * Get active addresses from addresses module
     */
    private function getActiveAddresses()
    {
        // Try to get from Addresses module
        try {
            return DB::table('mod_addresses_addresses')
                ->leftJoin('mod_addresses_provinces', 'mod_addresses_addresses.mod_addresses_address_province_id', '=', 'mod_addresses_provinces.mod_addresses_province_id')
                ->whereNull('mod_addresses_address_deleted_at')
                ->select([
                    'mod_addresses_address_id as id',
                    'mod_addresses_address_street_number',
                    'mod_addresses_address_street_name',
                    'mod_addresses_address_suburb',
                    'mod_addresses_address_city',
                    'mod_addresses_address_postal_code',
                    'mod_addresses_province_name as province_name',
                ])
                ->orderBy('mod_addresses_address_street_name')
                ->get()
                ->map(function ($addr) {
                    $addr->display_name = $addr->mod_addresses_address_street_number.' '.
                        $addr->mod_addresses_address_street_name.', '.
                        $addr->mod_addresses_address_suburb.', '.
                        $addr->mod_addresses_address_city.' '.
                        $addr->mod_addresses_address_postal_code;

                    return $addr;
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Log audit entry
     */
    private function logAudit($clientId, $action, $oldValues = null, $newValues = null)
    {
        ClientMasterAudit::create([
            'client_id' => $clientId,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Upload a document for a client
     * Creates versioned documents - never overwrites existing files
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $documentType
     * @return ClientMasterDocument
     */
    private function uploadDocument(ClientMaster $client, $file, DocumentType $documentType)
    {
        // Get file info
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        $codeType = $documentType->doc_group.' - '.$documentType->name;

        // Generate the stored filename with timestamp
        // Format: ClientCode CIPC - DocType - Uploaded Day DD Mon YYYY @ HH:MM:SS.ext
        $storedFilename = Document::generateStoredFilename(
            $client->client_code,
            $codeType,
            $extension
        );

        // Define storage path - store in client_docs folder (storage/app/public/client_docs)
        $storagePath = 'client_docs/'.$client->client_code;

        // Store the file

        $filePath = $file->storeAs($storagePath, $storedFilename, 'public');

        // Create the document record
        $document = Document::create([
            'client_id' => $client->client_id,
            'client_code' => $client->client_code,
            'title' => $storedFilename,
            // 'document_type' => $documentType,
            'document_ref' => $documentType->doc_group,
            'document_code' => $documentType->doc_ref,
            'doc_group' => $documentType->doc_group,
            'category_id' => $documentType->category_id,
            'type_id' => $documentType->id,
            'file_original_name' => $originalFilename,
            'file_stored_name' => $storedFilename,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'file_mime_type' => $mimeType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $document;
    }

    /**
     * Handle document upload with error handling and logging
     *
     * @param  string  $fieldName  The form field name to check for uploaded file
     * @param  string  $documentType  The type of document being uploaded
     * @param  string|null  $uploadedFlagField  Optional client field name to set to 1 when upload succeeds
     * @return bool Returns true if file was uploaded, false if no file was present
     *
     * @throws \Exception Re-throws any upload exceptions after logging
     */
    private function handleDocumentUpload(
        ClientMaster $client,
        Request $request,
        string $fieldName,
        ?DocumentType $documentType,
        ?string $uploadedFlagField = null
    ): bool {
        if (! $request->hasFile($fieldName)) {
            \Log::info("No file in request field '{$fieldName}' - skipping upload");

            return false;
        }

        \Log::info("{$documentType->name} - File detected for client: {$client->client_code}");

        try {
            $document = $this->uploadDocument($client, $request->file($fieldName), $documentType);

            // Set the uploaded flag if provided
            if ($uploadedFlagField) {
                $client->$uploadedFlagField = 1;
                if ($fieldName == 'cor_certificate') {
                    $client->cor_14_3_certificate = $document->file_stored_name;
                }
                if ($fieldName == 'income_tax_notice_registration_upload') {
                    $client->income_tax_registration = $document->file_stored_name;
                }
                if ($fieldName == 'payroll_notice_registration_upload') {
                    $client->payroll_registration = $document->file_stored_name;
                }
                if ($fieldName == 'vat_registration_upload') {
                    $client->vat_registration = $document->file_stored_name;
                }
                $client->save();
            }

            \Log::info("{$documentType->name} - SUCCESS");

            return true;

        } catch (\Exception $e) {
            \Log::error("{$documentType->name} - FAILED: {$e->getMessage()}");
            throw $e;
        }
    }

    public function clear_cache()
    {
        Artisan::call('optimize:clear');

        return response()->json(['success' => true, 'message' => 'Optimization cache cleared!']);
    }

    /**
     * Process multiple bank records for a client.
     * Creates new banks, updates existing banks, and soft-deletes specified banks.
     */
    private function processBanks(ClientMaster $client, Request $request): void
    {
        // Update existing banks — only is_checked status
        if ($request->has('existing_banks')) {
            foreach ($request->input('existing_banks', []) as $existingBank) {
                $client->bankAccounts()
                    ->where('id', $existingBank['id'])
                    ->update(['is_default' => filter_var($existingBank['is_default'], FILTER_VALIDATE_BOOLEAN)]);
            }
        }

        $proofFields = [
            'proof_of_bank_1' => null,
            'proof_of_bank_2' => null,
            'proof_of_bank_3' => null,
        ];

        // Create new bank records
        $banksData = $request->input('banks', []);
        $documentTypes = DocumentType::whereIn('doc_ref', [
            'BANK CONFIRM',
        ])->get()->keyBy('doc_ref');
        if (! empty($banksData)) {
            foreach ($banksData as $index => $bankData) {
                $bankRecord = ClientMasterBank::create([
                    'client_id' => $client->client_id,
                    'bank_id' => $bankData['bank_id'],
                    'bank_name' => $bankData['bank_name'] ?? null,
                    'bank_account_holder' => $bankData['bank_account_holder'] ?? null,
                    'bank_account_number' => $bankData['bank_account_number'] ?? null,
                    'bank_account_type_id' => $bankData['bank_account_type_id'] ?? null,
                    'bank_account_type_name' => $bankData['bank_account_type_name'] ?? null,
                    'bank_account_status_id' => $bankData['bank_account_status_id'] ?? null,
                    'bank_account_status_name' => $bankData['bank_account_status_name'] ?? null,
                    'bank_statement_frequency_id' => $bankData['bank_statement_frequency_id'] ?? null,
                    'bank_statement_frequency_name' => $bankData['bank_statement_frequency_name'] ?? null,
                    'bank_statement_cut_off_date' => $bankData['bank_statement_cut_off_date'] ?? null,
                    'bank_branch_name' => $bankData['bank_branch_name'] ?? null,
                    'bank_branch_code' => $bankData['bank_branch_code'] ?? null,
                    'bank_swift_code' => $bankData['bank_swift_code'] ?? null,
                    'bank_account_date_opened' => $bankData['bank_account_date_opened'] ?? null,
                    'is_checked' => false,
                    'is_default' => filter_var($bankData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);

                // Handle per-bank confirmation file upload
                $fileKey = "banks.{$index}.confirmation_file";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    $document = $this->uploadDocument($client, $file, $documentTypes->get('BANK CONFIRM'));
                    $bankRecord->confirmation_of_banking_uploaded = 1;
                    $bankRecord->document_id = $document->id;
                    $bankRecord->save();
                    $document->update([
                        'bank_id' => $bankRecord->id,
                        'bank_name' => $bankRecord->bank_name,
                    ]);

                    $proofField = 'proof_of_bank_'.($index + 1);
                    if (array_key_exists($proofField, $proofFields)) {
                        $proofFields[$proofField] = $document->file_stored_name;
                    }

                }
            }
        }

        foreach ($proofFields as $field => $value) {
            $client->$field = $value;
        }
        $client->save();

        // Soft-delete specified banks only
        $deletedBankIds = $request->input('deleted_bank_ids', []);
        if (! empty($deletedBankIds)) {
            ClientMasterBank::whereIn('id', $deletedBankIds)
                ->where('client_id', $client->client_id)
                ->delete();
        }

        \Log::info('Processed '.count($banksData).' new bank(s), updated existing banks, and deleted '.count($deletedBankIds)." bank(s) for client {$client->client_code}");
    }

    /**
     * Process client master addresses (from Address Details section)
     */
    // private function processAddressesSync(ClientMaster $client, Request $request): void
    // {
    //     // Update existing addresses — only default flag
    //     if ($request->has('existing_addresses')) {
    //         foreach ($request->input('existing_addresses', []) as $existingAddress) {
    //             $client->addresses()
    //                 ->where('id', $existingAddress['id'])
    //                 ->update([
    //                     'is_default' => filter_var($existingAddress['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
    //                 ]);
    //         }
    //     }

    //     // Create new address records
    //     $addressesData = $request->input('addresses', []);
    //     if (!empty($addressesData)) {
    //         foreach ($addressesData as $addressData) {
    //             ClientMasterAddress::create([
    //                 'client_id' => $client->client_id,
    //                 'address_id' => $addressData['address_id'] ?? null,
    //                 'address_type' => $addressData['address_type'] ?? null,
    //                 'is_checked' => false,
    //                 'is_default' => filter_var($addressData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
    //             ]);
    //         }
    //     }

    //     // Soft-delete specified addresses only
    //     $deletedAddressIds = $request->input('deleted_address_ids', []);
    //     if (!empty($deletedAddressIds)) {
    //         ClientMasterAddress::whereIn('id', $deletedAddressIds)
    //             ->where('client_id', $client->client_id)
    //             ->delete();
    //     }

    //     \Log::info("Processed " . count($addressesData) . " new address(es), updated existing addresses, and deleted " . count($deletedAddressIds) . " address(es) for client {$client->client_code}");
    // }

    /**
     * Upload a confirmation document for a specific bank record.
     */
    private function uploadBankDocument(ClientMaster $client, ClientMasterBank $bankRecord, $file): ClientMasterDocument
    {
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        $documentType = 'BANKING - Confirmation of Banking (Bank #'.$bankRecord->id.')';

        $storedFilename = ClientMasterDocument::generateStoredFilename(
            $client->client_code,
            $documentType,
            $extension
        );

        $storagePath = 'client_docs/'.$client->client_code;
        $filePath = $file->storeAs($storagePath, $storedFilename, 'public');

        return ClientMasterDocument::create([
            'client_id' => $client->client_id,
            'client_code' => $client->client_code,
            'document_type' => $documentType,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'uploaded_at' => now(),
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function get_client(int $id): JsonResponse
    {
        $client = ClientMaster::where('is_active',1)->findOrFail($id);

        $directors = ClientMasterDirector::where(['client_id' => $client->client_id, 'director_status_id' => 1])->latest()->get();

        return response()->json([
            'company_reg_number' => $client->company_reg_number,
            'client_code' => $client->client_code,
            'directors' => $directors,
        ]);
    }

    public function get_director(int $id): JsonResponse
    {
        $director = ClientMasterDirector::where('is_active',1)->findOrFail($id);
        $profile_photo = asset("storage/$director->profile_photo") ?: asset('smartdash/images/user.jpg');
        return response()->json([
            'director' => $director,
            'director_profile_image' => $profile_photo,
        ]);
    }

    
}
