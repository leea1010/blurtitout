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
        $jsonFile = $this->option('file') ?? base_path('scripts/therapists_result.json');
        $batchSize = (int) $this->option('batch-size');

        $this->info('[' . now() . '] Starting therapists data import...');
        $this->info('Source file: ' . $jsonFile);

        if (!file_exists($jsonFile)) {
            $this->warn('JSON file not found: ' . $jsonFile);
            return Command::SUCCESS; // Return success to avoid spam in logs
        }

        try {
            $this->importTherapistsData($jsonFile, $batchSize);
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

        $this->info("Found {$totalCount} therapists to process");

        // Process in batches to avoid memory issues
        $chunks = array_chunk($therapistsData, $batchSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            $this->info("Processing batch " . ($chunkIndex + 1) . "/" . count($chunks));

            foreach ($chunk as $therapistData) {
                // Skip if name is empty
                if (empty($therapistData['name']) || $therapistData['name'] === '') {
                    $skippedCount++;
                    continue;
                }

                // Check if therapist already exists by name
                $existingTherapist = Therapist::where('name', $therapistData['name'])->first();

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

        $this->info("Import completed!");
        $this->info("Total: {$totalCount}, Imported: {$importedCount}, Skipped: {$skippedCount}");

        Log::info('Therapists import completed', [
            'total' => $totalCount,
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'file' => $jsonFile
        ]);
    }

    /**
     * Prepare therapist data for database insertion
     */
    private function prepareTherapistData(array $therapistData): array
    {
        return [
            'avatar' => $this->cleanValue($therapistData['avatar'] ?? null),
            'avatar_list' => null,
            'name_prefix' => null,
            'name' => $this->cleanValue($therapistData['name']),
            'title' => $this->cleanValue($therapistData['title'] ?? null),
            'services_offered' => $this->cleanValue($therapistData['services_offered'] ?? null),
            'online_offered' => $this->cleanArray($therapistData['online_offered'] ?? null),
            'country' => $this->cleanValue($therapistData['country'] ?? null),
            'office_name' => null,
            'suit' => null,
            'street_address' => null,
            'city' => $this->cleanValue($therapistData['city'] ?? null),
            'zip_code' => null,
            'state' => $this->convertStateCodes($therapistData['state'] ?? null),
            'state_code' => $this->getFirstElement($therapistData['state_code'] ?? null),
            'gender' => $this->cleanValue($therapistData['gender'] ?? null),
            'email' => null,
            'phone_number' => null,
            'link_to_website' => $this->cleanValue($therapistData['link_to_website'] ?? null),
            'identifies_as_tag' => $this->cleanValue($therapistData['other_traits'] ?? null),
            'specialty' => $this->cleanArray($therapistData['specialty'] ?? null),
            'general_expertise' => $this->cleanArray($therapistData['general_expertise'] ?? null),
            'type_of_therapy' => $this->cleanValue($therapistData['type_of_therapy'] ?? null),
            'clinnical_approaches' => $this->cleanArray($therapistData['clinical_approaches'] ?? null),
            'about_1' => $this->cleanValue($therapistData['about'] ?? null),
            'about_2' => null,
            'insurance' => null,
            'payment_method' => $this->cleanArray($therapistData['payment_method'] ?? null),
            'fee' => null,
            'license' => $this->cleanValue($therapistData['license'] ?? null),
            'certification' => null,
            'education' => null,
            'experience' => $this->cleanValue($therapistData['experience'] ?? null),
            'experience_duration' => $this->cleanValue($therapistData['experience_duration'] ?? null),
            'serves_ages' => null,
            'community' => null,
            'languages' => $this->cleanArray($therapistData['languages'] ?? null) ?? json_encode(['English']),
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
