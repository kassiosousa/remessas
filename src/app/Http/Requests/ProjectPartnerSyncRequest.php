<?php
// app/Http/Requests/ProjectPartnerSyncRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProjectPartnerSyncRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'partners' => ['required','array','min:1'],
            'partners.*.partner_id' => ['required','integer','exists:partners,id'],
            'partners.*.share_percent' => ['required','numeric','min:0','max:100'],
            'partners.*.role' => ['nullable','string','max:100'],
            'partners.*.valid_from' => ['nullable','date'],
            'partners.*.valid_until' => ['nullable','date','after_or_equal:partners.*.valid_from'],

            // comportamento:
            'mode' => ['nullable','in:sync,attach,detach'], // default sync
            'detach_missing' => ['nullable','boolean'],      // para sync
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $partners = $this->input('partners', []);

            // se partners não é array, deixa as regras básicas cuidarem
            if (!is_array($partners)) {
                return;
            }

            $total = collect($partners)
                ->filter(fn ($p) => is_array($p) && array_key_exists('share_percent', $p))
                ->sum(fn ($p) => (float) $p['share_percent']);

            if ($total > 100) {
                $validator->errors()->add(
                    'partners',
                    "A soma de share_percent não pode ultrapassar 100. Total atual: {$total}"
                );
            }
        });
    }

}
