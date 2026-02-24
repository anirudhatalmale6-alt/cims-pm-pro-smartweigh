<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientMaster extends Model
{
    use SoftDeletes;

    protected $table = 'client_master';

    protected $primaryKey = 'client_id';

    protected $fillable = [
        // Company Information
        'company_name', 'company_reg_number', 'company_type', 'bizportal_number', 'company_reg_date',
        'trading_name', 'financial_year_end', 'month_no', 'client_code',
        'share_type_name',
        // Income Tax Registration
        'tax_number', 'tax_reg_date', 'cipc_annual_returns',
        // Payroll Registration
        'paye_number', 'sdl_number', 'uif_number', 'dept_labour_number',
        'wca_coida_number', 'payroll_liability_date',
        // VAT Registration
        'vat_number', 'vat_reg_date', 'vat_return_cycle', 'vat_cycle', 'vat_effect_from',
        // Contact Information
        'phone_business', 'phone_mobile', 'phone_whatsapp', 'email', 'website',
        // SARS Representative
        'sars_rep_first_name', 'sars_rep_middle_name', 'sars_rep_surname',
        'sars_rep_initial', 'sars_rep_title', 'sars_rep_gender',
        'sars_rep_id_number', 'sars_rep_id_type', 'sars_rep_id_issue_date',
        'sars_rep_tax_number', 'sars_rep_position', 'sars_rep_date_registered',
        // SARS E-Filing
        'sars_login', 'sars_password', 'sars_otp_mobile', 'sars_otp_email',
        // Banking Details
        'bank_account_holder', 'bank_account_number', 'bank_account_type',
        'bank_name', 'bank_branch_code',
        // Director Details
        'director_first_name', 'director_middle_name', 'director_surname',
        'director_initial', 'director_title', 'director_gender',
        'director_id_number', 'director_id_type', 'director_id_issue_date',
        'director_marital_status', 'director_marriage_type', 'director_marriage_date',
        // Partner Details
        'partner_first_name', 'partner_middle_name', 'partner_surname',
        'partner_title', 'partner_gender', 'partner_id_number',
        'partner_id_type', 'partner_id_issue_date',
        // Files
        'photo_path', 'signature_path',
        // Status
        'is_active', 'created_by', 'updated_by',
    ];

    protected $dates = [
        'company_reg_date', 'tax_reg_date', 'payroll_liability_date',
        'vat_reg_date', 'vat_effect_from', 'sars_rep_id_issue_date',
        'sars_rep_date_registered', 'director_id_issue_date',
        'director_marriage_date', 'partner_id_issue_date',
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function company_type()
    {
        return $this->belongsTo(CompanyType::class, 'company_type_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'client_id', 'client_id');
    }

    public function addresses()
    {
        return $this->hasMany(ClientMasterAddress::class, 'client_id', 'client_id');
    }

    public function directors()
    {
        return $this->hasMany(ClientMasterDirector::class, 'client_id', 'client_id');
    }

    public function audits()
    {
        return $this->hasMany(ClientMasterAudit::class, 'client_id', 'client_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(ClientMasterBank::class, 'client_id', 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'id');
    }

    /**
     * Get full director name
     */
    public function getDirectorFullNameAttribute()
    {
        $parts = array_filter([
            $this->director_first_name,
            $this->director_middle_name,
            $this->director_surname,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get full SARS representative name
     */
    public function getSarsRepFullNameAttribute()
    {
        $parts = array_filter([
            $this->sars_rep_first_name,
            $this->sars_rep_middle_name,
            $this->sars_rep_surname,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Generate a unique client code from company name
     * Format: 3 letters (first letter of each significant word) + 3 digits
     * Example: "Big Boy Bike Company" -> BBB100, "Charlies Angels Inc" -> CAI100
     */
    public static function generateClientCode($companyName = null)
    {
        if (empty($companyName)) {
            return '';
        }

        // Words to skip when building prefix
        $skipWords = ['and', 'the', 'of', 'for', 'in', 'on', 'at', 'to', 'a', 'an', 'by', 'with', 'pty', 'ltd', 'cc', 'inc', 'llc', 'limited', 'proprietary'];

        // Extract first letter of each significant word
        $words = preg_split('/\s+/', trim($companyName));
        $letters = [];

        foreach ($words as $word) {
            $cleanWord = strtolower(preg_replace('/[^a-zA-Z]/', '', $word));
            if (! empty($cleanWord) && ! in_array($cleanWord, $skipWords)) {
                $letters[] = strtoupper($cleanWord[0]);
            }
            // Stop at 3 letters
            if (count($letters) >= 3) {
                break;
            }
        }

        // If we have less than 3 letters, pad with first letters from first word
        if (count($letters) < 3 && ! empty($words)) {
            $firstWord = preg_replace('/[^a-zA-Z]/', '', $words[0]);
            while (count($letters) < 3 && strlen($firstWord) > count($letters)) {
                $letters[] = strtoupper($firstWord[count($letters)]);
            }
        }

        // If still less than 3, pad with X
        while (count($letters) < 3) {
            $letters[] = 'X';
        }

        $prefix = implode('', array_slice($letters, 0, 3));

        // Find all existing codes with this prefix
        $existingCodes = self::withTrashed()
            ->where('client_code', 'like', $prefix.'%')
            ->pluck('client_code')
            ->toArray();

        // Find the next available number (starting at 100, incrementing by 100)
        $number = 100;
        while (in_array($prefix.$number, $existingCodes)) {
            $number += 100;
        }

        return $prefix.$number;
    }

    /**
     * Check if a company name already exists
     */
    public static function companyNameExists($name, $excludeId = null)
    {
        $query = self::withTrashed()
            ->whereRaw('LOWER(company_name) = ?', [strtolower(trim($name))]);

        if ($excludeId) {
            $query->where('client_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Title case a company name (capitalize each word except small words)
     */
    public static function formatCompanyName($name)
    {
        $smallWords = ['and', 'the', 'of', 'for', 'in', 'on', 'at', 'to', 'a', 'an', 'by', 'with'];

        $words = explode(' ', trim($name));
        $result = [];

        foreach ($words as $index => $word) {
            $lowerWord = strtolower($word);

            // If word starts with any bracket, push as-is and skip formatting
            if (preg_match('/^[\(\{\[]/', $word)) {
                $result[] = $word;

                continue;
            }

            // First word always capitalized, small words lowercase (unless first)
            if ($index === 0 || ! in_array($lowerWord, $smallWords)) {
                $result[] = ucfirst($lowerWord);
            } else {
                $result[] = $lowerWord;
            }
        }

        return implode(' ', $result);
    }
}
