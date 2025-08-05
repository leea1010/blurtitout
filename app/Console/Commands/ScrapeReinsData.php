<?php

namespace App\Console\Commands;

use App\Models\Therapist;
use Illuminate\Console\Command;
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
            $this->runPythonScript($scriptPath, $PROXY_LIST);
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
                'avatar_list' => $this->cleanValue($therapistData['avatar_local_path'] ?? null),
                'name_prefix' => null,
                'name' => $this->cleanValue($therapistData['name']),
                'title' => $this->cleanValue($therapistData['title'] ?? null),
                'services_offered' => null,
                'online_offered' => null,
                'country' => null,
                'office_name' => null,
                'suit' => null,
                'street_address' => null,
                'city' => $this->cleanValue($therapistData['city'] ?? null),
                'zip_code' => null,
                'state' => null,
                'state_code' => null,
                'gender' => null,
                'email' => null,
                'phone_number' => null,
                'link_to_website' => null,
                'identifies_as_tag' => null,
                'specialty' => $this->cleanArray($therapistData['specialty'] ?? null),
                'general_expertise' => $this->cleanArray($therapistData['general_expertise'] ?? null),
                'type_of_therapy' => null,
                'clinnical_approaches' => null,
                'about_1' => null,
                'about_2' => null,
                'insurance' => null,
                'payment_method' => null,
                'fee' => null,
                'license' => null,
                'certification' => null,
                'education' => null,
                'experience' => null,
                'experience_duration' => $this->cleanValue($therapistData['experience_duration'] ?? null),
                'serves_ages' => null,
                'community' => null,
                'languages' => null,
                'faq' => null,
                'source' => 'BetterHelp',
            ];

            try {
                Therapist::create($dbData);
                $importedCount++;
            } catch (\Exception $e) {
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
