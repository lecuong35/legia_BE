<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('admin')->id() != null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:vouchers',
            'value' => 'required|min:0',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'value_condition' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Voucher đã tồn tại',
            'value.min' => 'Giá trị giảm giá phải lớn hơn 0',
            'value_condition.required' => 'Điền giá trị cho điều kiện nhận voucher',
            'start_date.after' => 'Thời gian bắt đầu phải lớn hơn thời gian hiện tại',
            'end_date.after' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu',
            'name.required' => 'Bạn chưa điền tên mã giảm giá',
            'value.required' => 'Bạn chưa điền giá trị áp dụng',
            'start_date.required' => 'Bạn chưa điền thời gian bắt đầu',
            'end_date.required' => 'Bạn chưa điền thời gian kết thúc',
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
