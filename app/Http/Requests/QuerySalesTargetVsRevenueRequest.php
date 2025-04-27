<?php

namespace App\Http\Requests;

use App\Models\Sales;
use Illuminate\Foundation\Http\FormRequest;

class QuerySalesTargetVsRevenueRequest extends FormRequest
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
            'sales_id' => ['sometimes', 'integer', 'exists:sales,id'],
        ];
    }

    /**
     * Get the sales from the validated request.
     *
     * @return \App\Models\Sales|null
     */
    public function getSales()
    {
        return isset($this->validated()['sales_id'])
            ? Sales::select('id', 'user_id')->with('user:id,name')->find($this->validated()['sales_id'])
            : null;
    }
}
