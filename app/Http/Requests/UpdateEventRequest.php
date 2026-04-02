<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->can('edit_events');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['messe', 'célébration', 'activité'])],
            'date_evenement' => ['required', 'date'],
            'heure_evenement' => ['nullable', 'date_format:H:i'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'celebre_par_id' => ['nullable', 'integer', 'exists:members,id'],
            'intention' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'paroisse_id' => Auth::check() && Auth::user()->hasRole('super_admin')
                ? ['required', 'integer', 'exists:paroisses,id']
                : ['nullable', 'integer', 'exists:paroisses,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titre' => $this->toUpper($this->input('titre')),
            'lieu' => $this->toTitleCase($this->input('lieu')),
            'intention' => $this->trimOrNull($this->input('intention')),
        ]);
    }

    private function toUpper(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        return $value !== null ? mb_strtoupper($value) : null;
    }

    private function toTitleCase(?string $value): ?string
    {
        $value = $this->trimOrNull($value);
        if ($value === null) {
            return null;
        }

        $value = mb_strtolower($value);

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
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
