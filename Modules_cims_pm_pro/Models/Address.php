<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'cims_addresses';

    protected $fillable = [
        'unit_number',
        'complex_name',
        'street_number',
        'street_name',
        'suburb',
        'city',
        'postal_code',
        'province',
        'country',
        'long_address',
        'google_address',
        'latitude',
        'longitude',
        'map_url',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $appends = ['tooltip'];
    /**
     * Build long address from components
     */
    public function buildLongAddress()
    {
        $parts = [];

        if (!empty($this->unit_number)) {
            $parts[] = $this->unit_number;
        }
        if (!empty($this->complex_name)) {
            $parts[] = $this->complex_name;
        }

        $streetParts = [];
        if (!empty($this->street_number)) {
            $streetParts[] = $this->street_number;
        }
        if (!empty($this->street_name)) {
            $streetParts[] = $this->street_name;
        }
        if (!empty($streetParts)) {
            $parts[] = implode(' ', $streetParts);
        }

        if (!empty($this->suburb)) {
            $parts[] = $this->suburb;
        }
        if (!empty($this->city)) {
            $cityPart = $this->city;
            if (!empty($this->postal_code)) {
                $cityPart .= ' ' . $this->postal_code;
            }
            $parts[] = $cityPart;
        }
        if (!empty($this->province)) {
            $parts[] = $this->province;
        }
        if (!empty($this->country)) {
            $parts[] = $this->country;
        }

        return implode(', ', $parts);
    }

    public function getTooltipAttribute(): string
    {
        $parts = [];
        
        if (!empty($this->unit_number)) {
            $parts[] = '<strong>Unit Number:</strong> ' . $this->unit_number;
        }
        
        if (!empty($this->complex_name)) {
            $parts[] = '<strong>Complex Name:</strong> ' . $this->complex_name;
        }
        
        if (!empty($this->street_number)) {
            $parts[] = '<strong>Street Number:</strong> ' . $this->street_number;
        }
        
        if (!empty($this->street_name)) {
            $parts[] = '<strong>Street Name:</strong> ' . $this->street_name;
        }
        
        if (!empty($this->suburb)) {
            $parts[] = '<strong>Suburb:</strong> ' . $this->suburb;
        }
        
        if (!empty($this->city)) {
            $parts[] = '<strong>City:</strong> ' . $this->city;
        }
        
        if (!empty($this->postal_code)) {
            $parts[] = '<strong>Postal Code:</strong> ' . $this->postal_code;
        }
        
        if (!empty($this->province)) {
            $parts[] = '<strong>Province:</strong> ' . $this->province;
        }
        
        if (!empty($this->country)) {
            $parts[] = '<strong>Country:</strong> ' . $this->country;
        }
        
        return implode('<br>', $parts);
    }

    /**
     * Auto-build long_address before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->long_address = $model->buildLongAddress();
        });
    }

    /**
     * Scope for active addresses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope for inactive addresses
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}
