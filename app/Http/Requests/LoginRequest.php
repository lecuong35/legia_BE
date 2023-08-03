<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'user.phone' => 'required|min:10|numeric',
            'user.password' => 'required|min:6',
        ];
    }

    public function messages()
    {
        return [
            'user.phone.required' => 'Số điện thoại không được để trống!',
            'user.phone.min' => 'Tối thiểu 10 chữ số',
            'user.phone.numeric' => 'Số điện thoại chỉ được chứa chữ số!',
            'user.password.required' => 'Mật khẩu không được để trống',
            'user.password.min' => 'Ít nhất 6 ký tự',
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
