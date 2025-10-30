<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportAllocateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'allocations' => ['required','array','min:1'],
            'allocations.*.project_id' => ['required','integer','exists:projects,id'],
            'allocations.*.project_net_amount' => ['required','numeric','min:0'],
            'allocations.*.currency' => ['nullable','size:3'],
            'allocations.*.units_sold' => ['nullable','integer','min:0'],
            'overwrite' => ['sometimes','boolean'], // se true, substitui as alocações existentes
        ];
    }

    public function prepareForValidation(): void
    {
        $alloc = collect($this->input('allocations', []))
            ->map(fn($a) => $a + ['currency' => $a['currency'] ?? 'USD'])
            ->toArray();
        $this->merge(['allocations' => $alloc, 'overwrite' => (bool)$this->input('overwrite', false)]);
    }
}
