<?php

namespace App\Services;

use League\Csv\Writer;
use League\Csv\Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CsvExportService
{
    /**
     * Export data to CSV
     *
     * @param Collection $data
     * @param array $headers
     * @param string $filename
     * @return Response
     * @throws Exception
     */
    public function export(Collection $data, array $headers, string $filename): Response
    {
        // Create CSV writer from memory
        $csv = Writer::createFromString();

        // Set UTF-8 BOM for proper Excel display
        $csv->setOutputBOM(Writer::BOM_UTF8);

        // Insert headers
        $csv->insertOne($headers);

        // Insert data
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $key => $header) {
                if (is_array($row)) {
                    $csvRow[] = $row[$key] ?? '';
                } else {
                    // For Eloquent models
                    $csvRow[] = $this->getValueFromObject($row, $key);
                }
            }
            $csv->insertOne($csvRow);
        }

        // Create response
        $response = new Response($csv->toString());

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Get value from object using dot notation
     *
     * @param mixed $object
     * @param string $key
     * @return string
     */
    private function getValueFromObject($object, string $key): string
    {
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $object;

            foreach ($keys as $nestedKey) {
                if (is_object($value) && property_exists($value, $nestedKey)) {
                    $value = $value->$nestedKey;
                } elseif (is_array($value) && isset($value[$nestedKey])) {
                    $value = $value[$nestedKey];
                } else {
                    return '';
                }
            }

            return $this->formatValue($value);
        }

        if (is_object($object)) {
            $value = $object->$key ?? '';

            // Debug logging for state and state_code
            if (in_array($key, ['state', 'state_code'])) {
                Log::info("Debug - Key: {$key}, Raw Value: " . var_export($value, true) . ", Type: " . gettype($value));
            }

            return $this->formatValue($value);
        }

        return $this->formatValue($object[$key] ?? '');
    }

    /**
     * Format value for CSV output
     *
     * @param mixed $value
     * @return string
     */
    private function formatValue($value): string
    {
        if (is_array($value)) {
            // Convert array to comma-separated string
            return implode(', ', array_filter($value));
        }

        // Handle JSON strings (including single quoted values)
        if (is_string($value)) {
            // Handle empty or null strings
            if (empty($value) || $value === 'null' || $value === '""') {
                return '';
            }

            // Check if it's a JSON-encoded single value (like "New York" or "NY")
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) && !str_contains($value, '[') && !str_contains($value, '{')) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                    return $decoded;
                }
            }

            // Check if it's a JSON array or object
            if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // If it's a simple array of values
                    if (array_is_list($decoded)) {
                        return implode(', ', array_filter($decoded));
                    }
                    // If it's an associative array, convert to key:value format
                    $pairs = [];
                    foreach ($decoded as $k => $v) {
                        if (is_string($v) || is_numeric($v)) {
                            $pairs[] = $k . ':' . $v;
                        }
                    }
                    return implode(', ', $pairs);
                }
            }
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_null($value)) {
            return '';
        }

        return (string) $value;
    }

    /**
     * Export Therapists to CSV
     *
     * @param Collection $therapists
     * @param string|null $filename
     * @return Response
     * @throws Exception
     */
    public function exportTherapists(Collection $therapists, ?string $filename = null): Response
    {
        $filename = $filename ?? 'therapists_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'avatar' => 'Avatar',
            'avatar_list' => 'Avatar list',
            'name_prefix' => 'Name_prefix',
            'name' => 'Name',
            'title' => 'Title',
            'services_offered' => 'Services offered',
            'online_offered' => 'Online_Offered',
            'country' => 'Country',
            'office_name' => 'Office Name',
            'suit' => 'Suit',
            'street_address' => 'Street address',
            'city' => 'City',
            'zip_code' => 'Zip Code',
            'state' => 'State',
            'state_code' => 'State Code',
            'gender' => 'Gender',
            'email' => 'Email',
            'phone_number' => 'Phone Number',
            'link_to_website' => 'Link to website',
            'identifies_as_tag' => 'Identifies_as_tag',
            'specialty' => 'Specialty',
            'general_expertise' => 'General Expertise',
            'type_of_therapy' => 'Type of Therapy',
            'clinnical_approaches' => 'Clinnical Approaches',
            'about_1' => 'About_1',
            'about_2' => 'About_2',
            'insurance' => 'Insurance',
            'payment_method' => 'Payment Method',
            'fee' => 'Fee',
            'license' => 'License',
            'certification' => 'Certification',
            'education' => 'Education',
            'experience' => 'Experience',
            'experience_duration' => 'Experience_Duration',
            'serves_ages' => 'Serves Ages',
            'community' => 'Community',
            'languages' => 'Languages',
            'faq' => 'FAQ',
            'source' => 'Source'
        ];

        return $this->export($therapists, $headers, $filename);
    }
    /**
     * Export Users to CSV
     *
     * @param Collection $users
     * @param string|null $filename
     * @return Response
     * @throws Exception
     */
    public function exportUsers(Collection $users, ?string $filename = null): Response
    {
        $filename = $filename ?? 'users_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'id' => 'ID',
            'name' => 'Tên',
            'email' => 'Email',
            'email_verified_at' => 'Email xác thực lúc',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật'
        ];

        return $this->export($users, $headers, $filename);
    }
}
