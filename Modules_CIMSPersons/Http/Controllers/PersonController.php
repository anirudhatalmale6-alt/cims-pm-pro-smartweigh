<?php

namespace Modules\CIMSPersons\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    /**
     * Person list page
     */
    public function index()
    {
        $page_title = 'Persons';
        $page_description = 'CIMS Person Management';
        return view('cimspersons::persons.manage', compact('page_title', 'page_description'));
    }

    /**
     * AJAX search persons
     */
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        $limit = (int)$request->input('limit', 12);
        $offset = (int)$request->input('offset', 0);

        $query = DB::table('cims_persons')
            ->select('*', DB::raw('firstname as first_names'), DB::raw('identity_number as id_number'))
            ->where('is_active', 1);

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('surname', 'LIKE', "%{$q}%")
                   ->orWhere('firstname', 'LIKE', "%{$q}%")
                   ->orWhere('identity_number', 'LIKE', "%{$q}%")
                   ->orWhere('email', 'LIKE', "%{$q}%")
                   ->orWhere('mobile_phone', 'LIKE', "%{$q}%");
            });
        }

        $total = $query->count();
        $rows = $query->orderBy('surname')->orderBy('firstname')
            ->offset($offset)->limit($limit)->get();

        return response()->json(['total' => $total, 'rows' => $rows]);
    }

    /**
     * Add person form
     */
    public function create()
    {
        $page_title = 'Add Person';
        $page_description = 'Capture new person details';
        $banks = DB::table('cims_ref_banks')->where('is_active', 1)->orderBy('bank_name')->get();
        $person = null;
        return view('cimspersons::persons.person', compact('page_title', 'page_description', 'banks', 'person'));
    }

    /**
     * Store person
     */
    public function store(Request $request)
    {
        try {
        $p = $request->all();

        // Clean identity number (remove spaces)
        $identityNumber = isset($p['identity_number']) ? str_replace(' ', '', $p['identity_number']) : null;

        // Helper function to parse display date format to database format (Y-m-d)
        // Handles: "Mon, 27 Jan 2026", "27 Jan 2026", "2026-01-27", etc.
        $parseDate = function($dateStr) {
            if (empty($dateStr)) return null;
            try {
                // Already in database format (Y-m-d)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                    return $dateStr;
                }
                // Format: "Mon, 27 Jan 2026" (with weekday prefix)
                $date = \DateTime::createFromFormat('D, j M Y', $dateStr);
                if ($date && $date->format('D, j M Y') === $dateStr) return $date->format('Y-m-d');
                // Format: "27 Jan 2026" (without weekday)
                $date = \DateTime::createFromFormat('j M Y', $dateStr);
                if ($date) return $date->format('Y-m-d');
                // Fallback to strtotime for other formats
                $timestamp = strtotime($dateStr);
                if ($timestamp) return date('Y-m-d', $timestamp);
                return null;
            } catch (\Exception $e) {
                return null;
            }
        };

        $row = [
            'citizenship'         => $p['citizenship'] ?? 'SOUTH AFRICAN',
            'identity_type'       => $p['identity_type'] ?? null,
            'identity_number'     => $identityNumber,
            'gender'              => $p['gender'] ?? null,
            'date_of_birth'       => $parseDate($p['date_of_birth'] ?? null),
            'date_of_issue'       => $parseDate($p['date_of_issue'] ?? null),
            'person_status'       => $p['person_status'] ?? 'Alive',
            'date_of_death'       => $parseDate($p['date_of_death'] ?? null),
            'ethnic_group'        => $p['ethnic_group'] ?? null,
            'disability'          => $p['disability'] ?? '0',
            'passport_number'     => $p['passport_number'] ?? null,
            'passport_expiry'     => $parseDate($p['passport_expiry'] ?? null),
            'country'             => $p['country'] ?? 'South Africa',
            'country_code'        => $p['country_code'] ?? null,
            'nationality'         => $p['nationality'] ?? 'South African',
            'title'               => $p['title'] ?? null,
            'initials'            => $p['initials'] ?? null,
            'surname'             => $p['surname'] ?? '',
            'firstname'           => $p['firstname'] ?? '',
            'middlename'          => $p['middlename'] ?? null,
            'known_as'            => $p['known_as'] ?? null,
            'tax_number'          => $p['tax_number'] ?? null,
            'mobile_phone'        => $p['mobile_phone'] ?? null,
            'whatsapp_number'     => $p['whatsapp_number'] ?? null,
            'office_phone'        => $p['office_phone'] ?? null,
            'other_phone'         => $p['other_phone'] ?? null,
            'email'               => $p['email'] ?? null,
            'accounts_email'      => $p['accounts_email'] ?? null,
            'marital_status'      => $p['marital_status'] ?? null,
            'marital_status_date' => $parseDate($p['marital_status_date'] ?? null),
            // Spouse fields
            'sp_citizenship'      => $p['sp_citizenship'] ?? null,
            'sp_identity_type'    => $p['sp_identity_type'] ?? null,
            'sp_identity_number'  => isset($p['sp_identity_number']) ? str_replace(' ', '', $p['sp_identity_number']) : null,
            'sp_date_of_birth'    => $parseDate($p['sp_date_of_birth'] ?? null),
            'sp_date_of_issue'    => $parseDate($p['sp_date_of_issue'] ?? null),
            'sp_person_status'    => $p['sp_person_status'] ?? null,
            'sp_gender'           => $p['sp_gender'] ?? null,
            'sp_ethnic_group'     => $p['sp_ethnic_group'] ?? null,
            'sp_disability'       => $p['sp_disability'] ?? null,
            'sp_title'            => $p['sp_title'] ?? null,
            'sp_initials'         => $p['sp_initials'] ?? null,
            'sp_tax_number'       => $p['sp_tax_number'] ?? null,
            'sp_firstname'        => $p['sp_firstname'] ?? null,
            'sp_middlename'       => $p['sp_middlename'] ?? null,
            'sp_surname'          => $p['sp_surname'] ?? null,
            'sp_known_as'         => $p['sp_known_as'] ?? null,
            'sp_mobile_phone'     => $p['sp_mobile_phone'] ?? null,
            'sp_whatsapp_number'  => $p['sp_whatsapp_number'] ?? null,
            'sp_office_phone'     => $p['sp_office_phone'] ?? null,
            'sp_other_phone'      => $p['sp_other_phone'] ?? null,
            'sp_email'            => $p['sp_email'] ?? null,
            'sp_accounts_email'   => $p['sp_accounts_email'] ?? null,
            // Address fields
            'complex_name'        => $p['complex_name'] ?? null,
            'address_line'        => $p['address_line'] ?? null,
            'address_line_2'      => $p['address_line_2'] ?? null,
            'suburb'              => $p['suburb'] ?? null,
            'city'                => $p['city'] ?? null,
            'postal_code'         => $p['postal_code'] ?? null,
            'province'            => $p['province'] ?? null,
            'address_country'     => $p['address_country'] ?? 'South Africa',
            'latitude'            => $p['latitude'] ?? null,
            'longitude'           => $p['longitude'] ?? null,
            // Banking fields
            'bank_account_holder' => $p['bank_account_holder'] ?? null,
            'bank_name'           => $p['bank_name'] ?? null,
            'bank_branch'         => $p['bank_branch'] ?? null,
            'bank_account_number' => isset($p['bank_account_number']) ? str_replace(' ', '', $p['bank_account_number']) : null,
            'bank_account_type'   => $p['bank_account_type'] ?? null,
            'bank_swift_code'     => $p['bank_swift_code'] ?? null,
            'bank_account_status' => $p['bank_account_status'] ?? null,
            'bank_date_opened'    => $parseDate($p['bank_date_opened'] ?? null),
            'notes'               => $p['notes'] ?? null,
            // Signature (base64 data URL stored directly or saved as file)
            'signature_image'     => $this->saveSignature($p['signature_data'] ?? null),
            'is_active'           => 1,
            'created_by'          => auth()->user()->first_name ?? 'System',
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        $id = DB::table('cims_persons')->insertGetId($row);

        return response()->json(['ok' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * View person
     */
    public function show($id)
    {
        $page_title = 'Person Details';
        $page_description = 'View person information';
        $person = DB::table('cims_persons')->where('id', (int)$id)->first();
        if (!$person) abort(404);

        $banks = DB::table('cims_ref_banks')->where('is_active', 1)->orderBy('bank_name')->get();

        $view_mode = true;
        return view('cimspersons::persons.person', compact('page_title', 'page_description', 'person', 'banks', 'view_mode'));
    }

    /**
     * Get person details by ID (AJAX)
     */
    public function get_person($id)
    {
        $person = DB::table('cims_persons')->where('id', (int)$id)->first();

        if (!$person) {
            return response()->json(['error' => 'Person not found'], 404);
        }

        return response()->json($person);
    }

    /**
     * Edit person form
     */
    public function edit($id)
    {
        $page_title = 'Edit Person';
        $page_description = 'Update person details';
        $person = DB::table('cims_persons')->where('id', (int)$id)->first();
        if (!$person) abort(404);

        $banks = DB::table('cims_ref_banks')->where('is_active', 1)->orderBy('bank_name')->get();

        return view('cimspersons::persons.person', compact('page_title', 'page_description', 'person', 'banks'));
    }

    /**
     * Update person
     */
    public function update(Request $request, $id)
    {
        try {
        $person = DB::table('cims_persons')->where('id', (int)$id)->first();
        if (!$person) abort(404);

        // Archive current version before updating (silently skip if fails)
        try {
            $archiveData = (array)$person;
            unset($archiveData['id']);
            $archiveData['person_id'] = $person->id;
            $archiveData['archived_at'] = now();
            $archiveData['archived_by'] = auth()->user()->first_name ?? 'System';
            // Remove fields not in archive table
            unset($archiveData['address_id'], $archiveData['profile_picture'], $archiveData['signature_upload']);
            unset($archiveData['is_active'], $archiveData['created_by'], $archiveData['created_at'], $archiveData['updated_at']);
            unset($archiveData['sars_login_name'], $archiveData['sars_login_password'], $archiveData['sars_cell_number'], $archiveData['sars_email_address']);
            unset($archiveData['bank_branch_name'], $archiveData['bank_date_opened']);
            unset($archiveData['home_phone'], $archiveData['direct_number'], $archiveData['spouse_initials']);
            unset($archiveData['profile_photo']); // New field not in archive

            DB::table('cims_persons_archive')->insert($archiveData);
        } catch (\Exception $archiveEx) {
            // Silently skip archiving if table structure doesn't match
            \Log::warning('Person archive failed: ' . $archiveEx->getMessage());
        }

        // Update the person
        $p = $request->all();

        // Clean identity number (remove spaces)
        $identityNumber = isset($p['identity_number']) ? str_replace(' ', '', $p['identity_number']) : null;

        // Helper function to parse display date format to database format (Y-m-d)
        // Handles: "Mon, 27 Jan 2026", "27 Jan 2026", "2026-01-27", etc.
        $parseDate = function($dateStr) {
            if (empty($dateStr)) return null;
            try {
                // Already in database format (Y-m-d)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                    return $dateStr;
                }
                // Format: "Mon, 27 Jan 2026" (with weekday prefix)
                $date = \DateTime::createFromFormat('D, j M Y', $dateStr);
                if ($date && $date->format('D, j M Y') === $dateStr) return $date->format('Y-m-d');
                // Format: "27 Jan 2026" (without weekday)
                $date = \DateTime::createFromFormat('j M Y', $dateStr);
                if ($date) return $date->format('Y-m-d');
                // Fallback to strtotime for other formats
                $timestamp = strtotime($dateStr);
                if ($timestamp) return date('Y-m-d', $timestamp);
                return null;
            } catch (\Exception $e) {
                return null;
            }
        };

        $update = [
            'citizenship'         => $p['citizenship'] ?? 'SOUTH AFRICAN',
            'identity_type'       => $p['identity_type'] ?? null,
            'identity_number'     => $identityNumber,
            'gender'              => $p['gender'] ?? null,
            'date_of_birth'       => $parseDate($p['date_of_birth'] ?? null),
            'date_of_issue'       => $parseDate($p['date_of_issue'] ?? null),
            'person_status'       => $p['person_status'] ?? 'Alive',
            'date_of_death'       => $parseDate($p['date_of_death'] ?? null),
            'ethnic_group'        => $p['ethnic_group'] ?? null,
            'disability'          => $p['disability'] ?? '0',
            'passport_number'     => $p['passport_number'] ?? null,
            'passport_expiry'     => $parseDate($p['passport_expiry'] ?? null),
            'country'             => $p['country'] ?? 'South Africa',
            'country_code'        => $p['country_code'] ?? null,
            'nationality'         => $p['nationality'] ?? 'South African',
            'title'               => $p['title'] ?? null,
            'initials'            => $p['initials'] ?? null,
            'surname'             => $p['surname'] ?? '',
            'firstname'           => $p['firstname'] ?? '',
            'middlename'          => $p['middlename'] ?? null,
            'known_as'            => $p['known_as'] ?? null,
            'tax_number'          => $p['tax_number'] ?? null,
            'mobile_phone'        => $p['mobile_phone'] ?? null,
            'whatsapp_number'     => $p['whatsapp_number'] ?? null,
            'office_phone'        => $p['office_phone'] ?? null,
            'other_phone'         => $p['other_phone'] ?? null,
            'email'               => $p['email'] ?? null,
            'accounts_email'      => $p['accounts_email'] ?? null,
            'marital_status'      => $p['marital_status'] ?? null,
            'marital_status_date' => $parseDate($p['marital_status_date'] ?? null),
            // Spouse fields
            'sp_citizenship'      => $p['sp_citizenship'] ?? null,
            'sp_identity_type'    => $p['sp_identity_type'] ?? null,
            'sp_identity_number'  => isset($p['sp_identity_number']) ? str_replace(' ', '', $p['sp_identity_number']) : null,
            'sp_date_of_birth'    => $parseDate($p['sp_date_of_birth'] ?? null),
            'sp_date_of_issue'    => $parseDate($p['sp_date_of_issue'] ?? null),
            'sp_person_status'    => $p['sp_person_status'] ?? null,
            'sp_gender'           => $p['sp_gender'] ?? null,
            'sp_ethnic_group'     => $p['sp_ethnic_group'] ?? null,
            'sp_disability'       => $p['sp_disability'] ?? null,
            'sp_title'            => $p['sp_title'] ?? null,
            'sp_initials'         => $p['sp_initials'] ?? null,
            'sp_tax_number'       => $p['sp_tax_number'] ?? null,
            'sp_firstname'        => $p['sp_firstname'] ?? null,
            'sp_middlename'       => $p['sp_middlename'] ?? null,
            'sp_surname'          => $p['sp_surname'] ?? null,
            'sp_known_as'         => $p['sp_known_as'] ?? null,
            'sp_mobile_phone'     => $p['sp_mobile_phone'] ?? null,
            'sp_whatsapp_number'  => $p['sp_whatsapp_number'] ?? null,
            'sp_office_phone'     => $p['sp_office_phone'] ?? null,
            'sp_other_phone'      => $p['sp_other_phone'] ?? null,
            'sp_email'            => $p['sp_email'] ?? null,
            'sp_accounts_email'   => $p['sp_accounts_email'] ?? null,
            // Address fields
            'complex_name'        => $p['complex_name'] ?? null,
            'address_line'        => $p['address_line'] ?? null,
            'address_line_2'      => $p['address_line_2'] ?? null,
            'suburb'              => $p['suburb'] ?? null,
            'city'                => $p['city'] ?? null,
            'postal_code'         => $p['postal_code'] ?? null,
            'province'            => $p['province'] ?? null,
            'address_country'     => $p['address_country'] ?? 'South Africa',
            'latitude'            => $p['latitude'] ?? null,
            'longitude'           => $p['longitude'] ?? null,
            // Banking fields
            'bank_account_holder' => $p['bank_account_holder'] ?? null,
            'bank_name'           => $p['bank_name'] ?? null,
            'bank_branch'         => $p['bank_branch'] ?? null,
            'bank_account_number' => isset($p['bank_account_number']) ? str_replace(' ', '', $p['bank_account_number']) : null,
            'bank_account_type'   => $p['bank_account_type'] ?? null,
            'bank_swift_code'     => $p['bank_swift_code'] ?? null,
            'bank_account_status' => $p['bank_account_status'] ?? null,
            'bank_date_opened'    => $parseDate($p['bank_date_opened'] ?? null),
            'notes'               => $p['notes'] ?? null,
            // Signature (base64 data URL stored directly or saved as file)
            'signature_image'     => $this->saveSignature($p['signature_data'] ?? null, $person->signature_image ?? null),
            'updated_at'          => now(),
        ];

        DB::table('cims_persons')->where('id', (int)$id)->update($update);

        return response()->json(['ok' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check duplicate ID number
     */
    public function checkDuplicate(Request $request)
    {
        $idNumber = trim($request->input('identity_number', ''));
        $excludeId = (int)$request->input('exclude_id', 0);

        if (empty($idNumber)) {
            return response()->json(['duplicate' => false]);
        }

        $query = DB::table('cims_persons')
            ->where('identity_number', $idNumber)
            ->where('is_active', 1);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existing = $query->first();

        return response()->json([
            'duplicate' => !!$existing,
            'person' => $existing ? [
                'id' => $existing->id,
                'name' => $existing->firstname . ' ' . $existing->surname
            ] : null
        ]);
    }

    /**
     * Get banks list (AJAX)
     */
    public function banks()
    {
        $banks = DB::table('cims_ref_banks')->where('is_active', 1)->orderBy('bank_name')->get();
        return response()->json($banks);
    }

    /**
     * Get bank accounts for a person
     */
    public function personBanks($id)
    {
        $banks = DB::table('cims_person_banks')
            ->where('person_id', (int)$id)
            ->orderByDesc('is_primary')
            ->orderBy('bank_name')
            ->get();
        return response()->json($banks);
    }

    /**
     * Add bank account to person
     */
    public function addBank(Request $request, $id)
    {
        $p = $request->all();

        // Parse date helper
        $parseDate = function($dateStr) {
            if (empty($dateStr)) return null;
            try {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) return $dateStr;
                $timestamp = strtotime($dateStr);
                if ($timestamp) return date('Y-m-d', $timestamp);
                return null;
            } catch (\Exception $e) {
                return null;
            }
        };

        $bankId = DB::table('cims_person_banks')->insertGetId([
            'person_id'           => (int)$id,
            'bank_name'           => $p['bank_name'] ?? null,
            'bank_branch_name'    => $p['bank_branch_name'] ?? null,
            'bank_branch_code'    => $p['bank_branch_code'] ?? null,
            'bank_account_name'   => $p['bank_account_name'] ?? null,
            'bank_account_number' => $p['bank_account_number'] ?? null,
            'bank_account_type'   => $p['bank_account_type'] ?? null,
            'bank_swift_code'     => $p['bank_swift_code'] ?? null,
            'bank_date_opened'    => $parseDate($p['bank_date_opened'] ?? null),
            'bank_account_status' => $p['bank_account_status'] ?? 'Active',
            'is_primary'          => $p['is_primary'] ?? 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
        return response()->json(['ok' => true, 'id' => $bankId]);
    }

    /**
     * Remove bank account from person
     */
    public function removeBank($id, $bankId)
    {
        DB::table('cims_person_banks')
            ->where('id', (int)$bankId)
            ->where('person_id', (int)$id)
            ->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Get addresses linked to a person
     */
    public function personAddresses($id)
    {
        $links = DB::table('cims_person_addresses as pa')
            ->join('cims_addresses as a', 'a.id', '=', 'pa.address_id')
            ->where('pa.person_id', (int)$id)
            ->select('pa.*', 'a.formatted_input', 'a.street_number', 'a.street_name', 'a.suburb', 'a.city', 'a.province', 'a.postal_code')
            ->orderBy('pa.address_type')
            ->get();
        return response()->json($links);
    }

    /**
     * Link address to person (one per type)
     */
    public function addAddress(Request $request, $id)
    {
        $addressId = (int)$request->input('address_id');
        $addressType = $request->input('address_type', 'Residential');

        // Check if type already used
        $existing = DB::table('cims_person_addresses')
            ->where('person_id', (int)$id)
            ->where('address_type', $addressType)
            ->first();

        if ($existing) {
            return response()->json(['ok' => false, 'error' => "This person already has a {$addressType} address. Remove it first."]);
        }

        $linkId = DB::table('cims_person_addresses')->insertGetId([
            'person_id'    => (int)$id,
            'address_id'   => $addressId,
            'address_type' => $addressType,
            'created_at'   => now(),
        ]);

        return response()->json(['ok' => true, 'id' => $linkId]);
    }

    /**
     * Unlink address from person
     */
    public function removeAddress($id, $linkId)
    {
        DB::table('cims_person_addresses')
            ->where('id', (int)$linkId)
            ->where('person_id', (int)$id)
            ->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Search addresses (for linking)
     */
    public function searchAddresses(Request $request)
    {
        $q = trim($request->input('q', ''));
        $query = DB::table('cims_addresses');
        if ($q) {
            $query->where('formatted_input', 'LIKE', "%{$q}%");
        }
        $rows = $query->orderBy('formatted_input')->limit(20)->get();
        return response()->json($rows);
    }

    /**
     * Save signature from base64 data URL
     */
    private function saveSignature($signatureData, $existingPath = null)
    {
        // If no new signature data, keep existing
        if (empty($signatureData)) {
            return $existingPath;
        }

        // Parse base64 data URL: data:image/png;base64,xxxxx
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $signatureData, $matches)) {
            $extension = $matches[1];
            $data = base64_decode($matches[2]);

            // Generate unique filename
            $filename = 'signatures/' . uniqid('sig_') . '.' . $extension;
            $fullPath = storage_path('app/public/' . $filename);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save file
            file_put_contents($fullPath, $data);

            // Delete old signature if exists
            if ($existingPath && file_exists(storage_path('app/public/' . $existingPath))) {
                @unlink(storage_path('app/public/' . $existingPath));
            }

            return $filename;
        }

        return $existingPath;
    }

    /**
     * Delete (soft) person
     */
    public function destroy($id)
    {
        DB::table('cims_persons')
            ->where('id', (int)$id)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        return redirect()->route('cimspersons.index')
            ->with('success', 'Person removed.');
    }
}
