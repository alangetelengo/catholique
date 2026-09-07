<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class QuickStoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('create_members')
            || $user->hasRole('super_admin')
        );
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prenom' => ['nullable', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'sexe' => ['required', 'in:M,F'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'paroisse_id' => ['nullable', 'integer', 'exists:paroisses,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            'email.email' => 'L’e-mail n’est pas valide.',
            'paroisse_id.exists' => 'La paroisse sélectionnée est invalide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'prenom' => $this->toTitleCase($this->input('prenom')) ?? '',
            'nom' => $this->toUpper($this->input('nom')),
            'email' => $this->toLower($this->input('email')),
            'telephone' => $this->normalizePhone($this->input('telephone')),
            'statut' => 'actif',
        ]);
    }

    private function toUpper(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        return $value !== null ? mb_strtoupper($value) : null;
    }

    private function toLower(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        return $value !== null ? mb_strtolower($value) : null;
    }

    private function toTitleCase(?string $value): ?string
    {
        $value = $this->trimOrNull($value);
        if ($value === null) {
            return null;
        }

        $value = mb_strtolower($value);

        return mb_convert_case($value, MB_CASE_TITLE);
    }

    private function normalizePhone(?string $value): ?string
    {
        $value = $this->trimOrNull($value);
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[()\s\-]/', '', $value) ?? $value;

        return $value;
    }

    private function trimOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
