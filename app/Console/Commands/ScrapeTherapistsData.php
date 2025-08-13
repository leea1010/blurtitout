<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ScrapeTherapistsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-therapists {--proxy-list=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously scrape therapists data from external sources using Python script';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scriptPath = base_path('scripts/main.py');
        $proxyList = $this->option('proxy-list') ? json_decode($this->option('proxy-list'), true) : [];
        $proxyJson = json_encode($proxyList);

        $this->info('Starting continuous Python scraping script...');
        $this->info('Script path: ' . $scriptPath);
        $this->info('Proxy list: ' . ($proxyJson === '[]' ? 'None' : $proxyJson));

        try {
            while (true) {
                $this->info('[' . now() . '] Running scraping cycle...');

                $this->runPythonScript($scriptPath, $proxyJson);

                $this->info('[' . now() . '] Scraping cycle completed successfully.');

                // Optional: Add a small delay between cycles to prevent overwhelming the target
                $this->info('Waiting 30 seconds before next cycle...');
                sleep(30);
            }
        } catch (ProcessFailedException $e) {
            $this->error('Python script failed: ' . $e->getMessage());
            Log::error('Scraping failed', [
                'error' => $e->getMessage(),
                'script_path' => $scriptPath,
                'proxy_list' => $proxyJson
            ]);
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Scraping error', [
                'error' => $e->getMessage(),
                'script_path' => $scriptPath
            ]);
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
        $process->setTimeout(null); // No timeout for continuous scraping
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error('Python process output', [
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput()
            ]);
            throw new ProcessFailedException($process);
        }

        // Log successful execution
        Log::info('Python script executed successfully', [
            'output' => $process->getOutput()
        ]);
    }
}
