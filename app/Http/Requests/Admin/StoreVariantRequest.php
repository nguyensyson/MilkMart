<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantRequest extends FormRequest
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
        $productId = $this->route('product')?->id;

        return [
            'sku' => ['nullable', 'string', 'max:50', 'unique:product_variants,sku'],
            'weight' => [
                'nullable', 'numeric', 'min:0',
                Rule::unique('product_variants', 'weight')->where('product_id', $productId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'weight.unique' => 'Sản phẩm này đã có biến thể với khối lượng tương tự.',
        ];
    }
}
