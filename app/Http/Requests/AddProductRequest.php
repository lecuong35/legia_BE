<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('admin')->id() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:products',
            'price' => 'required|regex:/^(\d+(,\d{1,2})?)?$/',
            'description' => 'required',
            'images' => 'required',
            'brand_id' => 'required',
            'category_id' => 'required',
            'quantity' => 'required',
            'code' => 'unique:products'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nhập tên cho sản phẩm!',
            'name.unique' => 'Tên sản phẩm đã tồn tại!',
            'price.required' => 'Nhập giá cho sản phẩm!',
            'price.regex' => 'Giá phải là số!',
            'images.required' => 'Chọn ảnh!',
            'category_id.required' => ' Chọn loại cho sản phẩm!',
            'brand_id.required' => ' Chọn thương hiệu cho sản phẩm!',
            'description.required' => 'Nhập miêu tả cho sản phẩm!',
            'quantity.required' => 'Nhập số lượng cho sản phẩm!',
            'code.unique' => 'Mã này không đúng!',
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
