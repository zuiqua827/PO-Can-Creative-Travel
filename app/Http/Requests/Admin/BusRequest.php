<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusRequest extends FormRequest
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
        $busId = $this->route('bus') ? $this->route('bus')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('buses', 'code')->ignore($busId)],
            'type' => ['required', 'string'],
            'seat_capacity' => ['required', 'integer', 'min:10', 'max:60'],
            'facilities' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'regenerate_seats' => ['nullable', 'boolean'],
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
            'name.required' => 'Nama armada bus wajib diisi.',
            'code.required' => 'Kode armada bus wajib diisi.',
            'code.unique' => 'Kode bus sudah digunakan oleh armada lain.',
            'type.required' => 'Tipe / kelas bus wajib dipilih.',
            'seat_capacity.required' => 'Kapasitas kursi wajib ditentukan.',
            'seat_capacity.min' => 'Kapasitas kursi minimal 10 kursi.',
            'seat_capacity.max' => 'Kapasitas kursi maksimal 60 kursi.',
            'status.required' => 'Status operasional bus wajib dipilih.',
        ];
    }
}
