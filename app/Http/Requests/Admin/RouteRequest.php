<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'string', 'max:100'],
            'estimated_duration' => ['nullable', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'origin.required' => 'Kota dan terminal asal wajib diisi.',
            'destination.required' => 'Kota dan terminal tujuan wajib diisi.',
            'base_price.required' => 'Tarif dasar tiket rute wajib diisi.',
            'base_price.min' => 'Tarif dasar tiket minimal Rp 10.000.',
            'status.required' => 'Status rute wajib dipilih.',
        ];
    }
}
