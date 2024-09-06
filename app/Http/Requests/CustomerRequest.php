<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerRequest extends FormRequest
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
//            'first_name' => 'required',
//            'last_name' => 'required',
//            'email' => 'required|email|unique:customers,email',
//            'telephone' => 'required',
//            'address' => 'required',
//            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'id_number' => 'required|unique:customers,id_number',
//        ];
        $rules = [
            'full_name' => 'required',
//            'last_name' => 'required',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dob' => 'nullable|date',
            'telephone' => 'nullable',
            'status' => 'nullable',
            'address' => 'nullable',
        ];

        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $rules['email'] = 'nullable|email|unique:customers,email,' . $this->route('customer');
//            $rules['id_number'] = 'required|unique:customers,id_number,' . $this->route('customer');
        } else {
            $rules['email'] = 'nullable|email|unique:customers,email';
//            $rules['id_number'] = 'required|unique:customers,id_number';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
