<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunitySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'     => ['required', 'in:execucao,premiacao,compromisso'],
            'start_number' => ['required', 'integer', 'min:1'],
        ];
    }
}
