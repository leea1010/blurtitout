<?php

namespace App\Console\Commands;

use App\Models\Therapist;
use Illuminate\Console\Command;

class ImportTherapistsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-therapists-data {--file=scripts/therapists_result.json : Path to JSON file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import therapists data from JSON file to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->option('file');
        $jsonFile = base_path($filePath);

        if (!file_exists($jsonFile)) {
            $this->error('JSON file not found: ' . $jsonFile);
            return Command::FAILURE;
        }

        $this->info('Reading JSON file: ' . $jsonFile);

        $jsonContent = file_get_contents($jsonFile);
        $therapistsData = json_decode($jsonContent, true);

        if (!$therapistsData || !is_array($therapistsData)) {
            $this->error('Invalid JSON data or empty file');
            return Command::FAILURE;
        }

        $this->info('Found ' . count($therapistsData) . ' therapists in JSON file');

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
                'avatar' => $therapistData['avatar'] ?? null,
                'avatar_list' => $therapistData['avatar_local_path'] ?? null,
                'name_prefix' => null,
                'name' => $therapistData['name'],
                'title' => $therapistData['title'] ?? null,
                'services_offered' => null,
                'online_offered' => null,
                'country' => null,
                'office_name' => null,
                'suit' => null,
                'street_address' => null,
                'city' => $therapistData['city'] ?? null,
                'zip_code' => null,
                'state' => null,
                'state_code' => null,
                'gender' => null,
                'email' => null,
                'phone_number' => null,
                'link_to_website' => null,
                'identifies_as_tag' => null,
                'specialty' => is_array($therapistData['specialty'] ?? null)
                    ? json_encode($therapistData['specialty'])
                    : json_encode([$therapistData['specialty'] ?? '']),
                'general_expertise' => is_array($therapistData['general_expertise'] ?? null)
                    ? json_encode($therapistData['general_expertise'])
                    : json_encode([]),
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
                'experience_duration' => $therapistData['experience_duration'] ?? null,
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
        $this->info("Skipped: {$skippedCount} therapists (duplicates or invalid data)");

        return Command::SUCCESS;
    }
}
