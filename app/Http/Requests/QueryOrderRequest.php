<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueryOrderRequest extends FormRequest
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
        return [
            'reference_no' => ['nullable', 'string', 'max:255'],
            'sales_id' => ['nullable', 'integer', 'exists:sales,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],

            'perPage' => ['integer', 'min:1'],
            'columns' => ['array'],
            'pageName' => ['string'],
            'page' => ['integer', 'min:1'],
        ];
    }
}
