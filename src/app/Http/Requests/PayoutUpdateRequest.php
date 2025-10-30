<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayoutUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'   => ['nullable','in:pending,scheduled,paid,canceled'],
            'due_date' => ['nullable','date'],
            'method'   => ['nullable','in:pix,transfer,paypal,wise,other'],
            'notes'    => ['nullable','string'],
        ];
    }
}
