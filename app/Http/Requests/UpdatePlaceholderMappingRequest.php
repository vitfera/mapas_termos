<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlaceholderMappingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'placeholder_key'   => ['required','string','max:100'],
            'placeholder_label' => ['required','string','max:255'],

            'opportunity_id'    => ['required','integer','exists:pgsql_remote.opportunity,id'],
            'field_id'          => ['required','integer','exists:pgsql_remote.registration_field_configuration,id'],
            'priority'          => ['required','integer','min:1'],
        ];
    }
}
