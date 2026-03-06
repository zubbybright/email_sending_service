<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Send email
     *
     * @param array $emailData
     * @return void
     */
    public function send(array $emailData): void
    {
        $personalization = $emailData['personalizations'][0];
        $subject         = $personalization['subject'];
        $recipients      = $personalization['to'];
        $from            = $emailData['from'];
        $body            = $this->extractBody($emailData['content']);

        $recipientEmails = array_column($recipients, 'email');

        try {
            Mail::raw($body, function ($message) use ($subject, $recipients, $from) {
                $message->subject($subject);
                $message->from($from['email'], $from['name'] ?? null);

                foreach ($recipients as $recipient) {
                    $message->to($recipient['email'], $recipient['name'] ?? null);
                }
            });

            Log::channel('mail')->info('Email sent', [
                'timestamp'  => now()->toIso8601String(),
                'recipients' => $recipientEmails,
                'subject'    => $subject,
                'status'     => 'success',
            ]);

        } catch (\Exception $exception) {
            Log::channel('mail')->error('Email failed', [
                'timestamp'  => now()->toIso8601String(),
                'recipients' => $recipientEmails,
                'subject'    => $subject,
                'status'     => 'failed',
                'error'      => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Extract plain text body from content array.
     * Falls back to the first available content value if text/plain is absent.
     *
     * @param array $content
     * @return string
     */
    private function extractBody(array $content): string
    {
        foreach ($content as $item) {
            if ($item['type'] === 'text/plain') {
                return $item['value'];
            }
        }

        return $content[0]['value'];
    }
}
