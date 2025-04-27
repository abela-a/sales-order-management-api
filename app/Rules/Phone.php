<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Phone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $abstractApi = new \App\Helpers\AbstractApi('phonevalidation');
            $phoneValidation = $abstractApi->get('phonevalidation', ['phone' => $value]);

            if ($phoneValidation['valid'] === false) {
                $fail('The :attribute is not a valid phone number.');
            }
        } catch (\Exception $e) {
            $pattern = '/^\+?[0-9]\d{1,14}$/';

            if (! preg_match($pattern, $value)) {
                $fail('The :attribute is not a valid phone number.');
            }
        }
    }
}
