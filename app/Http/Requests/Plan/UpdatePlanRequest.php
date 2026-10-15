<?php

namespace App\Http\Requests\Plan;

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var Plan $plan */
        $plan = $this->route('plan');
        $price = preg_replace('/\D/', '', (string) $this->plan_price); // "500.000" -> "500000"

        $this->merge([
            'plan_code' => strtoupper(trim((string) $this->plan_code)),
            'plan_price' => $price === '' ? null : (int) $price,
        ]);

        // Plan yang sudah punya pelanggan: scope & billing_type dikunci
        if ($plan->isUsed()) {
            $this->merge([
                'scope' => $plan->scope->value,
                'billing_type' => $plan->billing_type->value,
            ]);
        }
    }

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
            'plan_name' => ['required', 'string', 'max:100'],
            'scope' => ['required', Rule::enum(PlanScope::class)],
            'billing_type' => ['required', Rule::enum(BillingType::class)],
            'plan_price' => ['required', 'integer', 'min:0'],
            'plan_duration' => ['nullable', 'integer', 'min:1', 'max:65535', 'required_if:billing_type,recurring'],
            'is_active' => ['sometimes', 'boolean'],
            'features' => ['required', 'array', 'min:1'],
            'features.*' => ['distinct', Rule::enum(Feature::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'plan_code' => 'kode paket',
            'plan_name' => 'nama paket',
            'scope' => 'scope akses',
            'billing_type' => 'model penagihan',
            'plan_price' => 'harga paket',
            'plan_duration' => 'durasi aktif',
            'is_active' => 'status paket',
            'features' => 'hak akses fitur',
            'features.*' => 'fitur paket',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'plan_code.unique' => ':Attribute sudah terdaftar, gunakan kode lain.',
            'plan_code.regex' => ':Attribute hanya boleh huruf kapital, angka, strip (-), dan garis bawah (_).',
            'plan_price.min' => ':Attribute minimal 0.',
            'plan_duration.min' => ':Attribute minimal 1 hari.',
            'plan_duration.required_if' => ':Attribute wajib diisi untuk paket berulang.',
            'features.min' => 'Pilih minimal satu :attribute untuk paket ini.',
        ];
    }
}
