<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminLoginRequest extends FormRequest
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
            'admin.email' => 'required|email',
            'admin.password' => 'required|min:6',
        ];
    }

    public function messages()
    {
        return [
            'admin.email.required' => 'Email không được để trống',
            'admin.email.email' => 'Không đúng định dạng email',
            'admin.password.required' => 'Mật khẩu không được để trống',
            'admin.password.min' => 'Mật khẩu ít nhất 6 ký tự',
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
