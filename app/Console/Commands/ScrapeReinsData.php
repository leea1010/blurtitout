<?php

namespace App\Console\Commands;

use App\Models\Therapist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ScrapeReinsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-reins-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape data from REINS portal using Python script';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scriptPath = base_path('scripts/main.py');
        $PROXY_LIST = json_encode([]);

        try {
            $this->info('Starting Python scraping script...');
            // $this->runPythonScript($scriptPath, $PROXY_LIST);
            $this->info('Python script completed successfully.');

            // Import data to database after scraping
            $this->info('Importing data to database...');
            $this->importTherapistsData();

            return Command::SUCCESS;
        } catch (ProcessFailedException $e) {
            $this->error('Python script failed: ' . $e->getMessage());
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Run the Python script.
     *
     * @param string $scriptPath
     * @param string $proxyJson
     * @throws ProcessFailedException
     */
    private function runPythonScript(string $scriptPath, string $proxyJson): void
    {
        $venvPython = 'python';
        if (env('APP_ENV') === 'prod') {
            $venvPython = '/var/www/bukken-bank/venv/bin/python';
        }

        $process = new Process([$venvPython, $scriptPath, $proxyJson]);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
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

        // Filter out  values
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

    /**
     * Import therapists data from JSON file to database
     */
    private function importTherapistsData(): void
    {
        $jsonFile = base_path('scripts/therapists_result.json');

        if (!file_exists($jsonFile)) {
            $this->warn('JSON file not found: ' . $jsonFile);
            return;
        }

        $jsonContent = file_get_contents($jsonFile);
        $therapistsData = json_decode($jsonContent, true);

        if (!$therapistsData || !is_array($therapistsData)) {
            $this->error('Invalid JSON data or empty file');
            return;
        }

        $importedCount = 0;
        $skippedCount = 0;

        $this->output->progressStart(count($therapistsData));

        foreach ($therapistsData as $therapistData) {
            $this->output->progressAdvance();

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
            $dbData = [
                'avatar' => $this->cleanValue($therapistData['avatar'] ?? null),
                'avatar_list' => null,
                'name_prefix' => null, // Không có trong dữ liệu
                'name' => $this->cleanValue($therapistData['name']),
                'title' => $this->cleanValue($therapistData['title'] ?? null),
                'services_offered' => $this->cleanValue($therapistData['services_offered'] ?? null),
                'online_offered' => $this->cleanArray($therapistData['online_offered'] ?? null),
                'country' => $this->cleanValue($therapistData['country'] ?? null),
                'office_name' => null, // Không có trong dữ liệu
                'suit' => null, // Không có trong dữ liệu
                'street_address' => null, // Không có trong dữ liệu
                'city' => $this->cleanValue($therapistData['city'] ?? null),
                'zip_code' => null, // Không có trong dữ liệu
                'state' => $this->convertStateCodes($therapistData['state'] ?? null),
                'state_code' => $this->getFirstElement($therapistData['state_code'] ?? null),
                'gender' => $this->cleanValue($therapistData['gender'] ?? null),
                'email' => null, // Không có trong dữ liệu
                'phone_number' => null, // Không có trong dữ liệu
                'link_to_website' => $this->cleanValue($therapistData['link_to_website'] ?? null),
                'identifies_as_tag' => $this->cleanValue($therapistData['other_traits'] ?? null), // Map other_traits to identifies_as_tag
                'specialty' => $this->cleanArray($therapistData['specialty'] ?? null),
                'general_expertise' => $this->cleanArray($therapistData['general_expertise'] ?? null),
                'type_of_therapy' => $this->cleanValue($therapistData['type_of_therapy'] ?? null),
                'clinnical_approaches' => $this->cleanArray($therapistData['clinical_approaches'] ?? null),
                'about_1' => $this->cleanValue($therapistData['about'] ?? null),
                'about_2' => null, // Có thể chia about thành 2 phần nếu cần
                'insurance' => null, // Không có trong dữ liệu
                'payment_method' => $this->cleanArray($therapistData['payment_method'] ?? null),
                'fee' => null, // Không có trong dữ liệu
                'license' => $this->cleanValue($therapistData['license'] ?? null),
                'certification' => null, // Không có trong dữ liệu
                'education' => null, // Không có trong dữ liệu
                'experience' => $this->cleanValue($therapistData['experience'] ?? null),
                'experience_duration' => $this->cleanValue($therapistData['experience_duration'] ?? null),
                'serves_ages' => null, // Không có trong dữ liệu
                'community' => null, // Không có trong dữ liệu
                'languages' => $this->cleanArray($therapistData['languages'] ?? null) ?? json_encode(['English']),
                'faq' => null, // Không có trong dữ liệu
                'source' => 'BetterHelp',
            ];

            try {
                // Debug: Log processed data before creating
                Log::info('Processing therapist: ' . $therapistData['name']);
                Log::info('Processed data', $dbData);

                Therapist::create($dbData);
                $importedCount++;
            } catch (\Exception $e) {
                Log::error("Failed to import therapist: {$therapistData['name']} - " . $e->getMessage());
                Log::error('Original data', $therapistData);
                Log::error('Processed data', $dbData);
                $this->warn("Failed to import therapist: {$therapistData['name']} - " . $e->getMessage());
                $skippedCount++;
            }
        }

        $this->output->progressFinish();

        $this->info("Import completed!");
        $this->info("Imported: {$importedCount} therapists");
        $this->info("Skipped: {$skippedCount} therapists");
    }
}
