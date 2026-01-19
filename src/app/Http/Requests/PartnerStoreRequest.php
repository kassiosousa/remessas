<?php
// app/Http/Requests/PartnerStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'created_by' => ['required','exists:users,id'],
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:partners,email'],
            'portfolio' => ['nullable','string'],
            'birthday' => ['nullable','date'],
        ];
    }
}
