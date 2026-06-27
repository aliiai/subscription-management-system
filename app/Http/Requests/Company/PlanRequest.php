<?php

namespace App\Http\Requests\Company;

use App\Enums\BillingCycle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'currency' => ['required', 'string', 'max:8'],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'features' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attribute names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الخطة',
            'description' => 'الوصف',
            'price' => 'السعر',
            'currency' => 'العملة',
            'billing_cycle' => 'دورة الفوترة',
            'features' => 'المميزات',
        ];
    }

    /**
     * The plan attributes prepared for persistence.
     *
     * @return array<string, mixed>
     */
    public function planAttributes(): array
    {
        $features = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('features')))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return [
            'name' => $this->string('name')->value(),
            'description' => $this->input('description'),
            'price' => $this->float('price'),
            'currency' => $this->string('currency')->value(),
            'billing_cycle' => $this->string('billing_cycle')->value(),
            'features' => $features,
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
