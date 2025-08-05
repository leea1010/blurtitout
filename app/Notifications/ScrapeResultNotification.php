<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class ScrapeResultNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $status;

    public function __construct($data, $status = 'success')
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $message = new SlackMessage();

        if ($this->status === 'start') {
            return $message
                ->to('#all-bnksolution')
                ->content('REINS Data Scraping Started!')
                ->attachment(function ($attachment) {
                    $attachment
                        ->title('Scraping Started')
                        ->fields([
                            'Message' => $this->data['message'] ?? 'Scraping process initiated',
                            'Start Time' => $this->data['start_time'] ?? now()->format('Y-m-d H:i:s'),
                            'Command' => 'app:scrape-reins-data',
                        ])
                        ->color('#439FE0');
                });
        } elseif ($this->status === 'success') {
            return $message
                ->success()
                ->to('#all-bnksolution')
                ->content('REINS Data Scraping Completed Successfully!')
                ->attachment(function ($attachment) {
                    $attachment
                        ->title('Scraping Results')
                        ->fields([
                            'Total Records' => $this->data['total_records'] ?? 0,
                            'Successful Inserts' => $this->data['successful_inserts'] ?? 0,
                            'Failed Inserts' => $this->data['failed_inserts'] ?? 0,
                            'Execution Time' => $this->data['execution_time'] ?? '',
                            'Date' => now()->format('Y-m-d H:i:s'),
                        ])
                        ->color('good');
                });
        } else {
            return $message
                ->error()
                ->to('#all-bnksolution')
                ->content('REINS Data Scraping Failed!')
                ->attachment(function ($attachment) {
                    $attachment
                        ->title('Error Details')
                        ->fields([
                            'Error Message' => $this->data['error'] ?? 'Unknown error',
                            'Failed At' => now()->format('Y-m-d H:i:s'),
                            'Command' => 'app:scrape-reins-data',
                        ])
                        ->color('danger');
                });
        }
    }
}
