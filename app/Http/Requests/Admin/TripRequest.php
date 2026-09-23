<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TripRequest extends FormRequest
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
            'bus_id' => ['required', 'exists:buses,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'departure_at' => ['required', 'date'],
            'arrival_at' => ['required', 'date', 'after:departure_at'],
            'price' => ['required', 'numeric', 'min:10000'],
            'boarding_point' => ['nullable', 'string', 'max:255'],
            'drop_off_point' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:scheduled,boarding,departed,completed,cancelled'],
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
            'bus_id.required' => 'Armada bus wajib dipilih.',
            'bus_id.exists' => 'Armada bus yang dipilih tidak valid.',
            'route_id.required' => 'Rute perjalanan wajib dipilih.',
            'route_id.exists' => 'Rute perjalanan yang dipilih tidak valid.',
            'departure_at.required' => 'Waktu keberangkatan wajib diisi.',
            'arrival_at.required' => 'Waktu perkiraan tiba wajib diisi.',
            'arrival_at.after' => 'Waktu tiba harus setelah waktu keberangkatan.',
            'price.required' => 'Harga tiket per penumpang wajib diisi.',
            'price.min' => 'Harga tiket minimal Rp 10.000.',
            'status.required' => 'Status jadwal perjalanan wajib ditentukan.',
        ];
    }
}
