<?php

namespace App\Http\Requests\Plan;

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'plan_code' => $this->plan_code ? strtoupper(trim($this->plan_code)) : null,
            'plan_price' => ! is_null($this->plan_price) && trim((string) $this->plan_price) !== ''
                ? (int) preg_replace('/[^\d-]/', '', (string) $this->plan_price)
                : null,
            'plan_duration' => $this->billing_type === BillingType::OneTime->value ? null : $this->plan_duration,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_\-]+$/',
                Rule::unique('plans', 'code')->ignore($this->route('plan')),
            ],
            'plan_name' => [
                'required',
                'string',
                'max:100',
            ],
            'scope' => [
                'required',
                new Enum(PlanScope::class),
            ],
            'billing_type' => [
                'required',
                new Enum(BillingType::class),
            ],
            'plan_price' => [
                'required',
                'integer',
                'min:0',
            ],
            'plan_duration' => [
                Rule::requiredIf(fn (): bool => $this->billing_type === BillingType::Recurring->value),
                'nullable',
                'integer',
                'min:1',
            ],
            'features' => [
                'required',
                'array',
                'min:1',
            ],
            'features.*' => [
                'required',
                'string',
                new Enum(Feature::class),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'plan_code' => 'kode paket',
            'plan_name' => 'nama paket',
            'scope' => 'scope akses',
            'billing_type' => 'model penagihan',
            'plan_price' => 'harga paket',
            'plan_duration' => 'durasi aktif',
            'features' => 'hak akses fitur',
            'features.*' => 'fitur paket',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan_code.required' => ':Attribute wajib diisi.',
            'plan_code.unique' => ':Attribute sudah terdaftar, gunakan kode lain.',
            'plan_code.regex' => ':Attribute hanya boleh menggunakan huruf kapital, angka, strip (-), dan garis bawah (_).',
            'plan_name.required' => ':Attribute wajib diisi.',
            'scope.required' => ':Attribute wajib dipilih.',
            'billing_type.required' => ':Attribute wajib dipilih.',
            'plan_price.required' => ':Attribute wajib diisi.',
            'plan_price.min' => ':Attribute minimal bernilai 0.',
            'plan_duration.required' => ':Attribute wajib diisi untuk paket berulang.',
            'plan_duration.min' => ':Attribute minimal 1 hari.',
            'features.required' => 'Pilih minimal satu :attribute untuk paket ini.',
            'features.min' => 'Pilih minimal satu :attribute untuk paket ini.',
        ];
    }
}
