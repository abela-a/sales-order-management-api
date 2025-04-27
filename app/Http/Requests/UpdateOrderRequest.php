<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('order');

        return [
            'reference_no' => ['sometimes', 'string', 'max:255', 'unique:sales_orders,reference_no,'.$id],
            'sales_id' => ['sometimes', 'integer', 'exists:sales,id'],
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],

            'items' => ['sometimes', 'array'],
            'items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'items.*.product_id' => ['sometimes', 'integer', 'exists:products,id'],
        ];
    }
}
