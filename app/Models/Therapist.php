<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Therapist extends Model
{
    use HasFactory;

    protected $fillable = [
        'avatar',
        'avatar_list',
        'name_prefix',
        'name',
        'title',
        'services_offered',
        'online_offered',
        'country',
        'office_name',
        'suit',
        'street_address',
        'city',
        'zip_code',
        'state',
        'state_code',
        'gender',
        'email',
        'phone_number',
        'link_to_website',
        'identifies_as_tag',
        'specialty',
        'general_expertise',
        'type_of_therapy',
        'clinnical_approaches',
        'about_1',
        'about_2',
        'insurance',
        'payment_method',
        'fee',
        'license',
        'certification',
        'education',
        'experience',
        'experience_duration',
        'serves_ages',
        'community',
        'languages',
        'faq',
        'source',
    ];

    protected $casts = [
        'specialty' => 'array',
        'general_expertise' => 'array',
        'online_offered' => 'array',
        'state' => 'array',
        'state_code' => 'array',
        'clinnical_approaches' => 'array',
        'payment_method' => 'array',
        'languages' => 'array',
    ];

    // Custom accessor to handle double-encoded JSON
    public function getSpecialtyAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getGeneralExpertiseAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getOnlineOfferedAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getStateAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getStateCodeAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getClinnicalApproachesAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getPaymentMethodAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    public function getLanguagesAttribute($value)
    {
        return $this->decodeJsonAttribute($value);
    }

    /**
     * Helper method to decode JSON attributes
     */
    private function decodeJsonAttribute($value)
    {
        if (is_string($value)) {
            // Try to decode the JSON string
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // If it's still a string, try to decode again (double-encoded)
            if (is_string($decoded)) {
                $doubleDecoded = json_decode($decoded, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($doubleDecoded)) {
                    return $doubleDecoded;
                }
            }
        }
        return is_array($value) ? $value : [];
    }
}
