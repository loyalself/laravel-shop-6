<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

//8.5. 评价商品 新建: 评价商品数据验证
class SendReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return false; //default value
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
            'reviews'          => ['required', 'array'],
            'reviews.*.id'     => [
                'required',
                //$this->route('order') 可以获得当前路由对应的订单对象，Rule::exists() 判断用户提交的 ID 是否属于此订单
                Rule::exists('order_items', 'id')->where('order_id', $this->route('order')->id)
            ],
            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.review' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'reviews.*.rating' => '评分',
            'reviews.*.review' => '评价',
        ];
    }
}
