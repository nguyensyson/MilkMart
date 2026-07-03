<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class ApplyVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Existence/expiry/usage-limit checks happen in VoucherService::findForApply
        // instead of here, so each failure mode gets its own clear message.
        return [
            'voucher_code' => ['required', 'string', 'max:50'],
        ];
    }
}
