<?php

namespace App\Http\Requests\Company;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => [
                'nullable',
                Rule::exists('plans', 'id')->where(fn ($query) => $query->where('tenant_id', $this->user()->tenant_id)),
            ],
            'start_date' => ['nullable', 'date'],
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
            'name' => 'اسم العميل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'plan_id' => 'الخطة',
            'start_date' => 'تاريخ بدء الاشتراك',
        ];
    }
}
