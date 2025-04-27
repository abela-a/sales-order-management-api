<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class QuerySalesPerformanceStatisticReportRequest extends FormRequest
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
            'month' => 'sometimes|integer|between:1,12',
            'is_underperform' => 'sometimes|in:true,false,0,1',
        ];
    }

    /**
     * Retrieve the date range for sales performance statistics report.
     *
     * This method determines the start and end date of a month based on the
     * validated 'month' and 'year' inputs. If no values are provided,
     * it defaults to the current month and year.
     *
     * @return array An associative array containing:
     *               - 'start_date' Carbon instance set to the first day of the month
     *               - 'end_date' Carbon instance set to the last day of the month
     */
    public function getDateRange()
    {
        $month = $this->validated('month') ?? Carbon::now()->month;
        $year = $this->validated('year') ?? Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
