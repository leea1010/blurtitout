<?php

namespace App\Console\Commands;

use App\Models\Therapist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportTherapistsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-therapists {--file=} {--batch-size=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import therapists data from JSON file to database (runs every minute)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonFile = base_path('therapists_result_process_ForkPoolWorker-1.json');
        $jsonFile1 = base_path('therapists_result_process_ForkPoolWorker-2.json');
        $jsonFile2 = base_path('therapists_result.json');
        $batchSize = (int) $this->option('batch-size');

        $this->info('Source file: ' . $jsonFile);

        if (!file_exists($jsonFile)) {
            $this->warn('JSON file not found: ' . $jsonFile);
            return Command::SUCCESS; // Return success to avoid spam in logs
        }

        try {
            $this->importTherapistsData($jsonFile, $batchSize);
            $this->importTherapistsData($jsonFile1, $batchSize);
            $this->importTherapistsData($jsonFile2, $batchSize);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            Log::error('Therapists import failed', [
                'error' => $e->getMessage(),
                'file' => $jsonFile
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Import therapists data from JSON file to database
     */
    private function importTherapistsData(string $jsonFile, int $batchSize): void
    {
        $jsonContent = file_get_contents($jsonFile);
        $therapistsData = json_decode($jsonContent, true);

        if (!$therapistsData || !is_array($therapistsData)) {
            $this->warn('Invalid JSON data or empty file');
            return;
        }

        $importedCount = 0;
        $skippedCount = 0;
        $totalCount = count($therapistsData);


        // Process in batches to avoid memory issues
        $chunks = array_chunk($therapistsData, $batchSize);

        foreach ($chunks as $chunkIndex => $chunk) {

            foreach ($chunk as $therapistData) {
                // Skip if name is empty
                if (empty($therapistData['name']) || $therapistData['name'] === '') {
                    $skippedCount++;
                    continue;
                }
    $rawName = $therapistData['name'] ?? null;
        $namePrefix = null;
        $nameValue = $this->cleanValue($rawName);
        
        // Extract city and state from city field for duplicate checking
        $rawCity = $therapistData['city'] ?? null;
        $cityValue = $this->cleanValue($rawCity);
        $extractedStateFromCity = null;
        if (is_string($rawCity) && strpos($rawCity, ',') !== false) {
            $parts = explode(',', $rawCity, 2);
            $cityValue = $this->cleanValue($parts[0]);
            $extractedStateFromCity = $this->cleanValue($parts[1]);
        }

        if (is_string($rawName)) {
            // Match any characters before the first '.' and the rest after it
            if (preg_match('/^\s*([^\.]+)\.\s*(.+)$/u', $rawName, $matches)) {
                $namePrefix = $this->cleanValue($matches[1]);
                $nameValue = $this->cleanValue($matches[2]);
            }
        }
               
        // Check for existing therapist by name AND city to avoid duplicates
        $existingTherapist = Therapist::where('name', $nameValue)
                                     ->first();
        if ($existingTherapist) {
            $skippedCount++;
            continue;
        }

                // Prepare data for database
                $dbData = $this->prepareTherapistData($therapistData);

                try {
                    Therapist::create($dbData);
                    $importedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to import therapist: {$therapistData['name']} - " . $e->getMessage());
                    Log::error('Original data', $therapistData);
                    Log::error('Processed data', $dbData);
                    $skippedCount++;
                }
            }

            // Small delay between batches
            if ($chunkIndex < count($chunks) - 1) {
                usleep(100000); // 0.1 second
            }
        }
    }

    /**
     * Prepare therapist data for database insertion
     */
    private function prepareTherapistData(array $therapistData): array
    {
        // Handle name prefix like "Dr.", "Mr.", "Mrs." by splitting on the first dot
        $rawName = $therapistData['name'] ?? null;
        $namePrefix = null;
         $namePrefix = null;
        $cityValue = null;
        $nameValue = $this->cleanValue($rawName);

        if (is_string($rawName)) {
            // Match any characters before the first '.' and the rest after it
            if (preg_match('/^\s*([^\.]+)\.\s*(.+)$/u', $rawName, $matches)) {
                $namePrefix = $this->cleanValue($matches[1]);
                $nameValue = $this->cleanValue($matches[2]);
            }
        }

        // Handle city like "Point Baker, AK" -> extract city and state
        $rawCity = $therapistData['city'] ?? null;
        $cityValue = $this->cleanValue($rawCity);
        $extractedStateFromCity = null;
        if (is_string($rawCity) && strpos($rawCity, ',') !== false) {
            $parts = explode(',', $rawCity, 2);
            $cityValue = $this->cleanValue($parts[0]);
            $extractedStateFromCity = $this->cleanValue($parts[1]);
        }

        // Determine state input: prefer extracted state from city, otherwise use explicit 'state' field
        $stateInput = null;
        $stateCodeValue = null;
        
        if ($extractedStateFromCity !== null) {
            // If we have a state from city (like "AK" from "Wrangell, AK"), use it
            $stateInput = $extractedStateFromCity;
            $stateCodeValue = $extractedStateFromCity;
        } else {
            // Fallback to explicit 'state' field
            $stateInput = $therapistData['state'] ?? null;
            if (isset($therapistData['state'])) {
                $stateCodeValue = $this->getFirstElement($therapistData['state'] ?? null);
            }
        }

        return [
            'avatar' => $this->cleanValue($therapistData['avatar'] ?? null),
            'avatar_list' => null,
            'name_prefix' => $namePrefix,
            'name' => $nameValue,
            'title' => $this->cleanValue($therapistData['title'] ?? null),
            'services_offered' => $this->cleanValue($therapistData['services_offered'] ?? null),
            'online_offered' => $this->cleanArray($therapistData['online_offered'] ?? null),
            'country' => $this->cleanValue($therapistData['country'] ?? null),
            'office_name' => null,
            'suit' => null,
            'street_address' => null,
            'city' => $cityValue,
            'zip_code' => null,
            'state' => $this->convertStateCodes($stateInput ?? null),
            'state_code' => $this->cleanValue($stateCodeValue), // Use explicit 'state' or extracted value
            'gender' => $this->cleanValue($therapistData['gender'] ?? null),
            'email' => null,
            'phone_number' => null,
            'link_to_website' => $this->cleanValue($therapistData['link_to_website'] ?? null),
            'identifies_as_tag' => $this->cleanArray($therapistData['other_traits'] ?? null), // Map other_traits to identifies_as_tag
            'specialty' => $this->cleanArray(
                isset($therapistData['specialty']) && is_array($therapistData['specialty'])
                    ? array_map(function($item) {
                        return ucfirst(strtolower($item));
                    }, $therapistData['specialty'])
                    : $therapistData['specialty'] ?? null
            ),
            'general_expertise' => $this->cleanArray($therapistData['general_expertise'] ?? null),
            'type_of_therapy' => $this->cleanValue($therapistData['type_of_therapy'] ?? null),
            'clinnical_approaches' => $this->cleanArray($therapistData['clinical_approaches'] ?? null),
            'about_1' => $this->cleanValue($therapistData['about'] ?? null),
            'about_2' => null,
            'insurance' => null,
            'payment_method' => $this->cleanArray($therapistData['payment_method'] ?? null),
            'fee' => null,
            'license' => $this->cleanArray($therapistData['license_information'] ?? null),
            'certification' => null,
            'education' => null,
            'experience' => null,
            'experience_duration' => $this->cleanValue($therapistData['experience_duration'] ?? null),
            'serves_ages' => null,
            'community' => null,
            'languages' => $this->cleanArray(
                isset($therapistData['languages']) && is_array($therapistData['languages'])
                    ? array_map(function($item) {
                        return ucfirst(strtolower($item));
                    }, $therapistData['languages'])
                    : $therapistData['languages'] ?? null
            ),
            'faq' => null,
            'source' => 'BetterHelp',
        ];
    }

    /**
     * Clean value - return null if value is '', empty, or null
     */
    private function cleanValue($value): ?string
    {
        if ($value === null || $value === '' || $value === '') {
            return null;
        }
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Clean array - remove '' values and return null if empty or only contains ''
     */
    private function cleanArray($array): ?string
    {
        if (!is_array($array)) {
            if ($array === null || $array === '' || $array === '') {
                return null;
            }
            return json_encode([$array]);
        }

        // Filter out empty values
        $cleaned = array_values(array_filter($array, function ($item) {
            return $item !== null && $item !== '' && $item !== '';
        }));

        return empty($cleaned) ? null : json_encode($cleaned);
    }

    /**
     * Convert state codes to full state names
     */
    private function convertStateCodes($stateCodes): ?string
    {
        if (!is_array($stateCodes)) {
            if ($stateCodes === null || $stateCodes === '' || $stateCodes === '') {
                return null;
            }
            $stateCodes = [$stateCodes];
        }

        // Check if array is empty or first element is empty
        if (empty($stateCodes) || !isset($stateCodes[0]) || $stateCodes[0] === '' || $stateCodes[0] === null) {
            return null;
        }

        $stateMapping = [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
            'DC' => 'District of Columbia',
            'PR' => 'Puerto Rico',
            'VI' => 'U.S. Virgin Islands',
            'GU' => 'Guam',
            'AS' => 'American Samoa',
            'MP' => 'Northern Mariana Islands'
        ];

        // Get the first state code and convert it
        $firstCode = strtoupper(trim($stateCodes[0]));
        if (isset($stateMapping[$firstCode])) {
            return $stateMapping[$firstCode];
        }

        // If code not found, return original value
        return $firstCode;
    }

    /**
     * Get first element from array safely
     */
    private function getFirstElement($array): ?string
    {
        if (!is_array($array)) {
            if ($array === null || $array === '' || $array === '') {
                return null;
            }
            return $array;
        }

        // Check if array is empty or first element is empty
        if (empty($array) || !isset($array[0]) || $array[0] === '' || $array[0] === null) {
            return null;
        }

        return $this->cleanValue($array[0]);
    }
}
