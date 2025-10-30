<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayoutMarkPaidRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'paid_at'                 => ['nullable','date'],
            'method'                  => ['nullable','in:pix,transfer,paypal,wise,other'],
            'receipt'                 => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:8192'],
            'partner_invoice_number'  => ['nullable','string','max:100'],
            'partner_invoice'         => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:8192'],
            'notes'                   => ['nullable','string'],
        ];
    }
}
