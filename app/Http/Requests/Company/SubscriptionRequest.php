<?php

namespace App\Http\Requests\Company;

use App\Enums\SubscriptionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionRequest extends FormRequest
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
        $tenantId = $this->user()->tenant_id;

        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'plan_id' => [
                'required',
                Rule::exists('plans', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'start_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
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
            'customer_id' => 'العميل',
            'plan_id' => 'الخطة',
            'start_date' => 'تاريخ البدء',
            'status' => 'الحالة',
        ];
    }
}
