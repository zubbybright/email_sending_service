<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendMailTest extends TestCase
{
    private function validPayload(array $overrides = []): array
    {
        return array_merge_recursive([
            'personalizations' => [
                [
                    'to'      => [['email' => 'user@example.com', 'name' => 'John']],
                    'subject' => 'Welcome',
                ],
            ],
            'from'    => ['email' => 'no-reply@example.com', 'name' => 'Example App'],
            'content' => [['type' => 'text/plain', 'value' => 'Hello John']],
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // 1. Accepted request
    // -------------------------------------------------------------------------

    public function test_valid_request_returns_202(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/mail/send', $this->validPayload());

        $response->assertStatus(202)
                 ->assertJson(['message' => 'Email queued successfully']);
    }

    // -------------------------------------------------------------------------
    // 2. Validation failures – missing fields
    // -------------------------------------------------------------------------

    public function test_missing_personalizations_returns_422(): void
    {
        $payload = $this->validPayload();
        unset($payload['personalizations']);

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['personalizations']);
    }

    public function test_missing_subject_returns_422(): void
    {
        $payload = $this->validPayload();
        unset($payload['personalizations'][0]['subject']);

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['personalizations.0.subject']);
    }

    public function test_missing_from_returns_422(): void
    {
        $payload = $this->validPayload();
        unset($payload['from']);

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['from']);
    }

    public function test_missing_content_returns_422(): void
    {
        $payload = $this->validPayload();
        unset($payload['content']);

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['content']);
    }

    public function test_missing_to_returns_422(): void
    {
        $payload = $this->validPayload();
        unset($payload['personalizations'][0]['to']);

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['personalizations.0.to']);
    }

    // -------------------------------------------------------------------------
    // 3. Invalid email addresses
    // -------------------------------------------------------------------------

    public function test_invalid_recipient_email_returns_422(): void
    {
        $payload = $this->validPayload();
        $payload['personalizations'][0]['to'][0]['email'] = 'not-an-email';

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['personalizations.0.to.0.email']);
    }

    public function test_invalid_from_email_returns_422(): void
    {
        $payload = $this->validPayload();
        $payload['from']['email'] = 'not-an-email';

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['from.email']);
    }

    // -------------------------------------------------------------------------
    // 4. Multiple recipients
    // -------------------------------------------------------------------------

    public function test_multiple_recipients_are_accepted(): void
    {
        Queue::fake();

        $payload = $this->validPayload();
        $payload['personalizations'][0]['to'] = [
            ['email' => 'alice@example.com', 'name' => 'Alice'],
            ['email' => 'bob@example.com',   'name' => 'Bob'],
            ['email' => 'carol@example.com'],
        ];

        $this->postJson('/api/mail/send', $payload)
             ->assertStatus(202)
             ->assertJson(['message' => 'Email queued successfully']);
    }

    // -------------------------------------------------------------------------
    // 5. Job dispatched
    // -------------------------------------------------------------------------

    public function test_send_email_job_is_dispatched(): void
    {
        Queue::fake();

        $payload = $this->validPayload();

        $this->postJson('/api/mail/send', $payload);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) use ($payload) {
            $data = $job->emailData;

            return $data['personalizations'][0]['to'][0]['email'] === $payload['personalizations'][0]['to'][0]['email']
                && $data['personalizations'][0]['subject'] === $payload['personalizations'][0]['subject']
                && $data['from']['email'] === $payload['from']['email'];
        });
    }

    public function test_job_is_not_dispatched_on_invalid_request(): void
    {
        Queue::fake();

        $payload = $this->validPayload();
        unset($payload['personalizations']);

        $this->postJson('/api/mail/send', $payload);

        Queue::assertNothingPushed();
    }
}
