<?php
// app/Http/Requests/ProjectUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'created_by' => ['required','uuid','exists:users,id'],
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string','max:50'],
            'date_release' => ['nullable','date'],
            'finished' => ['nullable','boolean'],
            'url' => ['nullable','string','max:255'],
            'steam_id' => ['nullable','integer'],
            'capsule' => ['nullable','string','max:255'],
        ];
    }
}
