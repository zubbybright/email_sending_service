# Laravel Email Service API

A minimal Laravel API for email sending service with queue processing and file-based logging.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── SendMailController.php      # API endpoint controller
│   └── Requests/
│       └── SendMailRequest.php         # Request validation
├── Jobs/
│   └── SendEmailJob.php                # Queue job for email processing
└── Services/
    └── MailService.php                 # Business logic for email sending

config/
├── mail.php                             # Mail driver configuration (sendmail)
└── logging.php                          # File-based logging configuration

routes/
└── api.php                              # API routes definition

storage/
└── logs/                                # Application logs directory
```

## Components

### SendMailController
- Handles incoming POST requests to `/api/mail/send`
- Validates requests using `SendMailRequest`
- Dispatches `SendEmailJob` to the queue
- Returns 202 (Accepted) response

### SendMailRequest
- Validates required fields: `to`, `subject`, `body`
- Ensures `to` is a valid email address
- Limits `subject` to 255 characters
- Provides custom error messages

### SendEmailJob
- Implements `ShouldQueue` interface
- Processes queued email tasks
- Injects `MailService` for business logic
- Handles exceptions and logs failures
- Supports job retries on failure

### MailService
- Core business logic for email sending
- Uses Laravel Mail facade with sendmail driver
- Validates email data
- Handles errors and logging
- (Implementation pending)

## Configuration

### Mail Driver
- **Driver**: sendmail
- **Configuration**: `config/mail.php`
- **Sendmail Path**: `/usr/sbin/sendmail -t -i` (configurable via env)
- **From Address**: Configurable via `MAIL_FROM_ADDRESS` env variable

### Logging
- **Type**: File-based
- **Location**: `storage/logs/laravel.log`
- **Configuration**: `config/logging.php`
- **Log Level**: Configurable via `LOG_LEVEL` env variable

## API Endpoint

### POST /api/mail/send

**Request Body:**
```json
{
  "to": "recipient@example.com",
  "subject": "Email Subject",
  "body": "Email body content"
}
```

**Success Response (202 Accepted):**
```json
{
  "message": "Email queued for sending",
  "status": "pending"
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "to": ["Recipient email is required"],
    "subject": ["Email subject is required"],
    "body": ["Email body is required"]
  }
}
```

## Flow

1. **Request** → POST `/api/mail/send` with email data
2. **Validation** → `SendMailRequest` validates incoming data
3. **Queue Dispatch** → `SendMailController` dispatches `SendEmailJob`
4. **Response** → Returns 202 (Accepted) immediately
5. **Job Processing** → Queue worker processes `SendEmailJob`
6. **Logging** → Success/failure logged to `storage/logs/laravel.log`
7. **Email Sent** → `MailService` sends via sendmail driver (pending implementation)

## Next Steps

- [ ] Implement email sending logic in `MailService`
- [ ] Add email template support
- [ ] Add CC/BCC support
- [ ] Add attachment support
- [ ] Configure queue driver
- [ ] Add authentication middleware
- [ ] Add rate limiting
- [ ] Add tests

## Clean Architecture Principles Applied

- **Separation of Concerns**: Controller delegates to Service
- **Dependency Injection**: Services injected via constructor
- **Single Responsibility**: Each class has one purpose
- **Logging**: Centralized logging for debugging
- **Error Handling**: Graceful exception handling with logging
- **Validation**: Request validation before processing
- **Queue Processing**: Async job handling for scalability
