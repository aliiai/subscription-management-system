<?php

namespace App\Http\Requests\Company;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
            'invoice_id' => [
                'required',
                Rule::exists('invoices', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Add business-rule validation against the target invoice.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $invoice = $this->user()->tenant->invoices()->find($this->integer('invoice_id'));

            if ($invoice === null) {
                return;
            }

            if ($invoice->status === InvoiceStatus::Void) {
                $validator->errors()->add('invoice_id', 'لا يمكن تسجيل دفعة على فاتورة ملغاة.');

                return;
            }

            if ($this->float('amount') > $invoice->balance() + 0.001) {
                $validator->errors()->add('amount', 'المبلغ يتجاوز المتبقي على الفاتورة.');
            }
        });
    }

    /**
     * Custom attribute names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'invoice_id' => 'الفاتورة',
            'amount' => 'المبلغ',
            'paid_at' => 'تاريخ الدفع',
            'method' => 'طريقة الدفع',
            'reference' => 'المرجع',
        ];
    }
}
