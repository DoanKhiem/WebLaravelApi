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
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'percent' => 'required',
//            'detail' => 'required',
            'file' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'Nhập hình ảnh',
            'title.required' => 'Nhập tiêu đề',
            'percent.required' => 'Nhập phần trăm',
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
