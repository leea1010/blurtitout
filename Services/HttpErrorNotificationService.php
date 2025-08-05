<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class HttpErrorNotificationService
{
    /**
     * Gửi email thông báo lỗi HTTP.
     *
     * @param int $statusCode
     * @param string $url
     * @param string $errorMessage
     */
    public function sendErrorNotification($statusCode, $url, $errorMessage)
    {
        $to = 'receiver_email@example.com'; // Email người nhận
        $subject = "HTTP Error {$statusCode} Detected";
        $body = "An HTTP error occurred:\n\n" .
                "Status Code: {$statusCode}\n" .
                "URL: {$url}\n" .
                "Error Message: {$errorMessage}";

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });

            logger()->info("Error notification email sent successfully for status code {$statusCode}.");
        } catch (\Exception $e) {
            logger()->error("Failed to send error notification email: {$e->getMessage()}");
        }
    }
}