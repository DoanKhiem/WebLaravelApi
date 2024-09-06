<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class PackageRequest extends FormRequest
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
        $rules = [
            'title' => 'required',
            'amount ' => 'required|numeric',
//            'detail' => '',
        ];

//        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
//            $rules['image'] = 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
//            $rules['file'] = 'sometimes|required|file';
//        } else {
//            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
//            $rules['file'] = 'required|file';
//        }

        return $rules;
    }

    public function messages()
    {
        return [
//            'title.required' => 'Nhập tiêu đề',
//            'percent.required' => 'Nhập phần trăm',
//            'image.required' => 'Nhập hình ảnh',
//            'detail.required' => 'Nhập chi tiết',
//            'file.nullable' => 'Nhập file',
        ];
    }

//    protected function failedValidation(Validator $validator)
//    {
//        $errors = $validator->errors();
//
//        throw new HttpResponseException(
//            response()->json([
//                'success' => false,
//                'message' => 'Validation failed',
//                'errors' => $errors
//            ], 422)
//        );
//    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
