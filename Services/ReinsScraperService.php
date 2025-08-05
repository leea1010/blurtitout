<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ReinsScraperService
{
    public function run()
    {
        Log::info("Bắt đầu gọi Python script...");

        // Đường dẫn chính xác đến file Python
        $scriptPath = base_path('scripts/scrape_reins.py');
        $process = new Process(['python3', $scriptPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error("Python script thất bại: " . $process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        Log::info("Python script thành công. Đang đọc file all_scraped_data.json...");

        // Đường dẫn chính xác đến file all_scraped_data.json
        $jsonPath = base_path('all_scraped_data.json');
        if (!file_exists($jsonPath)) {
            Log::error("File all_scraped_data.json không tồn tại.");
            return;
        }

        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        if (!$data || !isset($data['data'])) {
            Log::error("File all_scraped_data.json không đúng định dạng.");
            return;
        }

        // Lưu dữ liệu vào DB
        foreach ($data['data'] as $item) {
            \App\Models\PropertySaleHistory::updateOrCreate(
                ['property_number' => $item['property_number']],
                [
                    'price_total' => $item['price'],
                    'registered_at' => now(),
                ]
            );
            Log::info("Lưu thành công property_number: {$item['property_number']}");
        }

        Log::info("Hoàn thành toàn bộ quy trình!");
    }
}
