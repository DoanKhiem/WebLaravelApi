<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientRequest extends FormRequest
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
//            'email' => 'required|email|unique:clients,email',
//            'telephone' => 'required',
//            'address' => 'required',
//            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'id_number' => 'required|unique:clients,id_number',
//        ];
        $rules = [
            'full_name' => 'required',
//            'last_name' => 'required',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dob' => 'required|date',
            'contact_number' => 'required',
            'status' => 'nullable',
            'gender' => 'required',
//            'address' => 'nullable',
        ];

        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $rules['email'] = 'email|unique:clients,email,' . $this->route('client');
//            $rules['id_number'] = 'required|unique:clients,id_number,' . $this->route('client');
        } else {
            $rules['email'] = 'email|unique:clients,email';
//            $rules['id_number'] = 'required|unique:clients,id_number';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
