<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMailRequest;
use App\Jobs\SendEmailJob;
use App\Services\MailService;
use Illuminate\Http\JsonResponse;

class SendMailController extends Controller
{
    /**
     * Mail service instance
     *
     * @var MailService
     */
    protected MailService $mailService;

    /**
     * Constructor
     *
     * @param MailService $mailService
     */
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Send email endpoint
     *
     * @param SendMailRequest $request
     * @return JsonResponse
     */
    public function send(SendMailRequest $request): JsonResponse
    {
        // Validate request
        $validated = $request->validated();

        // Dispatch job to queue
        SendEmailJob::dispatch($validated);

        // Return response
        return response()->json([
            'message' => 'Email queued successfully',
        ], 202);
    }
}
