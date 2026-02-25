<?php

namespace Modules\CIMSDocManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\cims_pm_pro\Models\ClientMaster;
use Illuminate\Http\Request;
use Modules\cims_pm_pro\Models\Document;
use Modules\cims_pm_pro\Models\DocumentCategory;
use Modules\cims_pm_pro\Models\DocumentType;
use Modules\cims_pm_pro\Models\DocumentPeriod;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;

class DocManagerController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Display listing of documents
     */
    public function index(Request $request)
    {
        $query = Document::with(['category', 'type', 'period'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($qb) use ($q) {
                $qb->where('title', 'LIKE', "%{$q}%")
                   ->orWhere('document_code', 'LIKE', "%{$q}%")
                   ->orWhere('client_name', 'LIKE', "%{$q}%")
                   ->orWhere('client_code', 'LIKE', "%{$q}%")
                   ->orWhere('registration_number', 'LIKE', "%{$q}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        $documents = $query->paginate(20);

        $stats = [
            'total' => Document::active()->count(),
            'this_month' => Document::active()->whereMonth('created_at', now()->month)->count(),
            'expiring_soon' => Document::active()->expiringSoon(30)->count(),
        ];

        $categories = DocumentCategory::active()->orderBy('name')->get();

        return view('documents.index', compact('documents', 'stats', 'categories'));
    }

    /**
     * Show form for creating a new document
     */
    public function create(Request $request)
    {
        $categories = DocumentCategory::active()->orderBy('name')->get();
        $types = DocumentType::active()->orderBy('name')->get();
        $periods = DocumentPeriod::active()->ordered()->get();
        $clients = $this->getClients();

        // Pre-select client if passed in URL
        $selectedClientId = $request->input('client_id');

        return view('documents.form', compact(
            'categories', 'types', 'periods', 'clients', 'selectedClientId'
        ));
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|max:102400', // 100MB max - made optional for testing
            'category_id' => 'required|exists:cims_document_categories,id',
            'type_id' => 'required|exists:cims_document_types,id',
            'client_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle file upload (optional for testing)
            $originalName = null;
            $extension = null;
            $storagePath = 'client_docs/'.$request->client_code;

            $documentType = DocumentType::find($request->type_id);

            $codeType = $documentType->doc_group . " - " .$documentType->name;
        
        if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $storedFilename = Document::generateStoredFilename(
                    $request->client_code,
                    $codeType,
                    $extension
                );
                $uploadPath = $file->storeAs($storagePath, $storedFilename, 'public');
                // $file->move(public_path($uploadPath), $storedName);
            }

            // Get type details for auto-fill
            $type = DocumentType::find($request->type_id);
            $period = $request->period_id ? DocumentPeriod::find($request->period_id) : null;

            // Build description message
            $description = $this->buildDocumentDescription($request, $type, $period);

            $document = new Document();
            $document->title = $request->title;
            $document->document_code = $type ? $type->doc_ref : $request->document_code;
            $document->document_ref = $type ? $type->doc_group : null;
            $document->file_original_name = $originalName;
            $document->file_stored_name = $storedFilename;
            $document->file_mime_type = $extension ? strtolower($extension) : null;
            $document->file_path = $uploadPath;
            $document->client_id = $request->client_id;
            $document->client_name = $request->client_name;
            $document->client_code = $request->client_code;
            $document->client_email = $request->client_email;
            $document->registration_number = $request->registration_number;
            $document->category_id = $request->category_id;
            $document->type_id = $request->type_id;
            $document->doc_group = $type ? $type->doc_group : null;
            $document->period_id = $request->period_id;
            $document->period_name = $period ? $period->period_name : null;
            $document->period_combo = $period ? $period->period_combo : null;
            $document->financial_year = $request->financial_year;
            $document->issue_date = $request->issue_date;
            $document->expiry_date = $request->expiry_date;
            $document->date_registered = now();
            $document->has_expiry = $request->has_expiry ?? 'NO';
            $document->lead_time_days = $request->lead_time_days ?? 0;
            $document->status = 'Current';
            $document->description = $description;
            $document->notes = $request->notes;
            $document->uploaded_by = auth()->user()->name ?? 'System';
            $document->created_by = auth()->id();
            $document->save();

            DB::commit();

            return redirect()->route("cimsdocmanager.index")
                ->with('success', 'Document "' . $document->title . '" uploaded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error uploading document: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified document
     */
    public function show($id)
    {
        $document = Document::with(['category', 'type', 'period'])->findOrFail($id);

        return view('documents.show', compact('document'));
    }

    /**
     * Show form for editing the specified document
     */
    public function edit($id)
    {
        $document = Document::findOrFail($id);
        $categories = DocumentCategory::active()->orderBy('name')->get();
        $types = DocumentType::active()->orderBy('name')->get();
        $periods = DocumentPeriod::active()->ordered()->get();
        $clients = $this->getClients();

        return view('documents.form', compact(
            'document', 'categories', 'types', 'periods', 'clients'
        ));
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|max:102400',
            'category_id' => 'required|exists:cims_document_categories,id',
            'type_id' => 'required|exists:cims_document_types,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle file upload if new file provided
            if ($request->hasFile('file')) {
                // Delete old file
                $oldPath = public_path("storage/".$document->file_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $storagePath = 'client_docs/'.$request->client_code;
                $documentType = DocumentType::find($request->type_id);
                $codeType = $documentType->doc_group . " - " .$documentType->name;
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $storedFilename = Document::generateStoredFilename(
                    $request->client_code,
                    $codeType,
                    $extension
                );

                $uploadPath = $file->storeAs($storagePath, $storedFilename, 'public');


                $document->file_original_name = $originalName;
                $document->file_stored_name = $storedFilename;
                $document->file_mime_type = strtolower($extension);
                $document->file_path = $uploadPath . $storedFilename;
            }

            // Get type details
            $type = DocumentType::find($request->type_id);
            $period = $request->period_id ? DocumentPeriod::find($request->period_id) : null;

            $document->title = $request->title;
            $document->document_code = $type ? $type->doc_ref : $request->document_code;
            $document->document_ref = $type ? $type->doc_group : null;
            $document->client_id = $request->client_id;
            $document->client_name = $request->client_name;
            $document->client_code = $request->client_code;
            $document->client_email = $request->client_email;
            $document->registration_number = $request->registration_number;
            $document->category_id = $request->category_id;
            $document->type_id = $request->type_id;
            $document->doc_group = $type ? $type->doc_group : null;
            $document->period_id = $request->period_id;
            $document->period_name = $period ? $period->period_name : null;
            $document->period_combo = $period ? $period->period_combo : null;
            $document->financial_year = $request->financial_year;
            $document->issue_date = $request->issue_date;
            $document->expiry_date = $request->expiry_date;
            $document->has_expiry = $request->has_expiry ?? 'NO';
            $document->lead_time_days = $request->lead_time_days ?? 0;
            $document->notes = $request->notes;
            $document->updated_by = auth()->id();
            $document->save();

            DB::commit();

            return redirect()->route("cimsdocmanager.index")
                ->with('success', 'Document "' . $document->title . '" updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating document: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        try {
            // Delete file
            $filePath = public_path($document->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $document->delete();

            return redirect()->route("cimsdocmanager.index")
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }

    /**
     * Download document
     */
    public function download($id)
    {
        $document = Document::findOrFail($id);
        $filePath = base_path('../storage/' . $document->file_path);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $document->file_stored_name);
    }


    /**
     * Preview document (for iframe/viewer)
     */
    public function preview($id)
    {
        $document = Document::findOrFail($id);

        return view('documents.preview', compact('document'));
    }

    /**
     * Get document types by category (AJAX)
     */
    public function getTypesByCategory($categoryId)
    {
        $types = DocumentType::active()
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'name', 'doc_ref', 'doc_group', 'has_expiry', 'lead_time_days']);

        return response()->json($types);
    }

    /**
     * Search clients (AJAX)
     */
    public function searchClients(Request $request)
    {
        $q = $request->input('q', '');

        // Try to get clients from ClientMaster module if available
        if (class_exists('Modules\ClientMaster\Models\ClientMaster')) {
            $clients = ClientMaster::where('company_name', 'LIKE', "%{$q}%")
                ->orWhere('client_code', 'LIKE', "%{$q}%")
                ->limit(20)
                ->get(['client_id as id', 'company_name as name', 'client_code as code', 'email', 'company_reg_number as reg_number']);
        } else {
            // Fallback to direct query
            $clients = DB::table('client_master')
                ->where('company_name', 'LIKE', "%{$q}%")
                ->orWhere('client_code', 'LIKE', "%{$q}%")
                ->limit(20)
                ->get(['client_id as id', 'company_name as name', 'client_code as code', 'email', 'company_reg_number as reg_number']);
        }

        return response()->json($clients);
    }

    /**
     * Get clients for dropdown
     */
    private function getClients()
    {
        if (class_exists('Modules\ClientMaster\Models\ClientMaster')) {
            return ClientMaster::orderBy('company_name')
                ->get(['client_id as id', 'company_name as name', 'client_code as code', 'email', 'company_reg_number as reg_number']);
        }

        return DB::table('client_master')
            ->orderBy('company_name')
            ->get(['client_id as id', 'company_name as name', 'client_code as code', 'email', 'company_reg_number as reg_number']);
    }

    /**
     * Build document description message
     */
    private function buildDocumentDescription($request, $type, $period)
    {
        $clientName = $request->client_name ?? '';
        $clientCode = $request->client_code ?? '';
        $typeName = $type ? $type->name : $request->title;
        $periodName = $period ? $period->period_name : '';
        $docRef = $type ? $type->doc_ref : '';
        $regNumber = $request->registration_number ?? '';
        $uploadedBy = auth()->user()->name ?? 'System';
        $uploadDate = now()->format('D, d M Y @ g:i A');

        return "{$clientName} [ {$clientCode} ] {$typeName} for period [ {$periodName} ] with Document Reference [ {$docRef} ] and Company Registration No. {$regNumber}, this document was uploaded by {$uploadedBy} on {$uploadDate}";
    }

    public function view($document)
    {
        // dd($document);
        $document = Document::findOrFail($document);

        $filePath = base_path('../storage/' . $document->file_path);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return view('documents.view_client', compact('document'));

        // $mimeType = $document->mime_type ?? 'application/octet-stream';
        // return response()->file($filePath, [
        //     'Content-Type' => $mimeType,
        //     'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"'
        // ]);
    }

     public function view_client($client_id, $document)
    {
        // dd($document);

        $client = ClientMaster::findOrFail($client_id);
        $document = Document::where(['client_id'=>$client_id,'file_stored_name' => $client->{$document}])->get()->first();

        $filePath = base_path('../storage/' . $document->file_path);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return view('documents.view_client', compact('document'));
    }
}
