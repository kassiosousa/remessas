<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => ['nullable','string','max:255'],
            'platform'     => ['required','in:steam,epic,xbox,playstation,switch,android,ios,itch'],
            'period_month' => ['required','regex:/^\d{4}-\d{2}$/'], // YYYY-MM
            'currency'     => ['required','size:3'],
            'gross_amount' => ['required','numeric','min:0'],
            'fees'         => ['nullable','numeric','min:0'],
            'taxes'        => ['nullable','numeric','min:0'],
            'net_amount'   => ['nullable','numeric','min:0'],
            'statement_ref'=> ['nullable','string','max:100'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'fees' => $this->input('fees', 0),
            'taxes' => $this->input('taxes', 0),
            'net_amount' => $this->filled('net_amount')
                ? $this->input('net_amount')
                : max(0, ($this->float('gross_amount') ?? 0) - ($this->float('fees') ?? 0) - ($this->float('taxes') ?? 0)),
        ]);
    }
}
