<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'name' => 'required',
            'password' => 'required|min:6',
            'phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users',
            'location' => 'required',
            'email' => 'required|email|unique:users'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên không được để trống',
            'password.required' => 'Mật khẩu không được để trống',
            'password.min' => 'Mật khẩu chứa tối thiểu 6 ký tự',
            'phone.min' => 'Tối thiểu 10 chữ số',
            'phone.regex' => 'Số điện thoại không đúng',
            'phone.unique' => 'Số điện thoại đã được đăng ký',
            'location.required' => 'Địa chỉ không được để trống',
            'email.required' => 'Email khong duoc de trong',
            'email.email' => 'Sai dinh dang email',
            'email.unique' => 'Email da duoc dang ky'
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
