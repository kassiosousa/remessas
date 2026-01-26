<?php
// app/Http/Requests/PartnerUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'created_by' => ['required','uuid','exists:users,id'],
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:partners,email'],
            'portfolio' => ['nullable','string'],
            'birthday' => ['nullable','date'],
        ];
    }
}
