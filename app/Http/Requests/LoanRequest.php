<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoanRequest extends FormRequest
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
//        return [
//            'client_id' => 'required',
//            'package_id' => 'required',
//            'payment_period' => 'required',
//            'document_type' => 'required',
//            'nid_driver_license_number' => 'required|numeric',
//            'nid_driver_license_file' => 'required|file',
//            'work_id_number' => 'required|numeric',
//            'work_id_file' => 'required|file',
//            'selfie' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'pay_slip_1' => 'required|file',
//            'pay_slip_2' => 'required|file',
//            'pay_slip_3' => 'required|file',
//            'fn_pay_amount_1' => 'nullable|numeric',
//            'fn_pay_amount_2' => 'nullable|numeric',
//            'fn_pay_amount_3' => 'nullable|numeric',
//            'next_fn_pay' => 'nullable|date',
//        ];

        $rules = [
            'client_id' => 'required',
            'package_id' => 'required',
            'payment_period' => 'required',
            'document_type' => 'required',
            'nid_driver_license_number' => 'required|numeric',
//            'nid_driver_license_file' => 'required|file',
            'work_id_number' => 'required|numeric',
//            'work_id_file' => 'required|file',
//            'selfie' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'pay_slip_1' => 'required|file',
//            'pay_slip_2' => 'required|file',
//            'pay_slip_3' => 'required|file',
            'fn_pay_amount_1' => 'nullable|numeric',
            'fn_pay_amount_2' => 'nullable|numeric',
            'fn_pay_amount_3' => 'nullable|numeric',
            'next_fn_pay' => 'nullable|date',
        ];

        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $rules['nid_driver_license_file'] = 'nullable|file';
            $rules['work_id_file'] = 'nullable|file';
            $rules['selfie'] = 'nullable|file';
            $rules['pay_slip_1'] = 'nullable|file';
            $rules['pay_slip_2'] = 'nullable|file';
            $rules['pay_slip_3'] = 'nullable|file';
        } else {
            $rules['nid_driver_license_file'] = 'required|file';
            $rules['work_id_file'] = 'required|file';
            $rules['selfie'] = 'required|file';
            $rules['pay_slip_1'] = 'required|file';
            $rules['pay_slip_2'] = 'required|file';
            $rules['pay_slip_3'] = 'required|file';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
