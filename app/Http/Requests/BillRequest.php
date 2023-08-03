<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address' => 'required',
            'customer_phone' => 'required|min:10|string|max:12|regex:/^[0-9]+$/',
        ];
    }

    public function messages()
    {
        return [
            'address.required' => 'Bạn chưa điền địa chỉ nhận hàng',
            'customer_phone.required' => 'Bạn chưa điền số điện thoại nhận hàng',
            'customer_phone.regex' => 'Số điện thoại chỉ chứa các chữ số',
            'customer_phone.min' => 'Số điện thoại chứa nhiều hơn 9 chữ số',
            'customer_phone.max' => 'Số điện thoại chứa ít hơn 12 chữ số',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => $errors->messages(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
