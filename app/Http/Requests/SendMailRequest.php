<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'personalizations'                   => 'required|array|min:1',
            'personalizations.*.to'              => 'required|array|min:1',
            'personalizations.*.to.*.email'      => 'required|email',
            'personalizations.*.to.*.name'       => 'sometimes|string',
            'personalizations.*.subject'         => 'required|string|max:255',
            'from'                               => 'required|array',
            'from.email'                         => 'required|email',
            'from.name'                          => 'sometimes|string',
            'content'                            => 'required|array|min:1',
            'content.*.type'                     => 'required|string',
            'content.*.value'                    => 'required|string',
        ];
    }

    /**
     * Get custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'personalizations.required'              => 'Personalizations are required',
            'personalizations.*.to.required'         => 'Each personalization must have at least one recipient',
            'personalizations.*.to.*.email.required' => 'Recipient email is required',
            'personalizations.*.to.*.email.email'    => 'Recipient email must be a valid email address',
            'personalizations.*.subject.required'    => 'Email subject is required',
            'personalizations.*.subject.max'         => 'Email subject cannot exceed 255 characters',
            'from.required'                          => 'Sender information is required',
            'from.email.required'                    => 'Sender email is required',
            'from.email.email'                       => 'Sender email must be a valid email address',
            'content.required'                       => 'Email content is required',
            'content.*.type.required'                => 'Content type is required',
            'content.*.value.required'               => 'Content value is required',
        ];
    }
}
