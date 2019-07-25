<?php

namespace App\Http\Requests;

use App\Rules\ValidateLatLongRule;

class NewOrderRequest extends CommonFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin' => [
                'required',
                'array',
                new ValidateLatLongRule

            ],
            'destination' => [
                'required',
                'array',
                new ValidateLatLongRule
            ],
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'origin.required' => 'REQ_ORIGIN',
            'destination.required' => 'REQ_DESTINATION',
        ];
    }
}
