<?php

namespace App\Http\Requests\Booking;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seats' => ['required', 'array', 'min:1'],
            'seats.*' => ['required', 'integer', 'exists:bus_seats,id'],
            'passengers' => ['required', 'array'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.phone' => ['required', 'string', 'max:20'],
            'passengers.*.id_number' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'seats.required' => 'Pilihan kursi tidak boleh kosong.',
            'seats.min' => 'Pilih minimal 1 kursi.',
            'passengers.required' => 'Data penumpang wajib diisi.',
            'passengers.*.name.required' => 'Nama lengkap setiap penumpang wajib diisi.',
            'passengers.*.phone.required' => 'Nomor WhatsApp / telepon penumpang wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
