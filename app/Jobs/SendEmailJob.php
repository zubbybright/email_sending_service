<?php

namespace App\Jobs;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Email data
     *
     * @var array
     */
    public array $emailData;

    /**
     * Create a new job instance
     *
     * @param array $emailData
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Execute the job
     *
     * @param MailService $mailService
     * @return void
     */
    public function handle(MailService $mailService): void
    {
        try {
            // Send email via service
            $mailService->send($this->emailData);

            Log::info('Email sent successfully', [
                'recipient' => $this->emailData['personalizations'][0]['to'][0]['email'] ?? 'unknown',
                'subject' => $this->emailData['personalizations'][0]['subject'] ?? 'unknown',
            ]);
        } catch (\Exception $exception) {
            Log::error('Failed to send email', [
                'recipient' => $this->emailData['personalizations'][0]['to'][0]['email'] ?? 'unknown',
                'subject' => $this->emailData['personalizations'][0]['subject'] ?? 'unknown',
                'error' => $exception->getMessage(),
            ]);

            // Re-throw the exception to retry
            throw $exception;
        }
    }
}
