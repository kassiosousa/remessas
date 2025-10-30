<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => ['nullable','string','max:255'],
            'platform'     => ['nullable','in:steam,epic,xbox,playstation,switch,android,ios,itch'],
            'period_month' => ['nullable','regex:/^\d{4}-\d{2}$/'],
            'currency'     => ['nullable','size:3'],
            'gross_amount' => ['nullable','numeric','min:0'],
            'fees'         => ['nullable','numeric','min:0'],
            'taxes'        => ['nullable','numeric','min:0'],
            'net_amount'   => ['nullable','numeric','min:0'],
            'statement_ref'=> ['nullable','string','max:100'],
        ];
    }
}
