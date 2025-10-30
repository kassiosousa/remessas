<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayoutGenerateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'due_date' => ['nullable','date'],      // data padrão para todos
            'reset_existing' => ['sometimes','boolean'], // se true, apaga pendentes e regenera
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['reset_existing' => (bool)$this->input('reset_existing', false)]);
    }
}
