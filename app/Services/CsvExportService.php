<?php

namespace App\Services;

use League\Csv\Writer;
use League\Csv\Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;

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
            'id' => 'ID',
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
