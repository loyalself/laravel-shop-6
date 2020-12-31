<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//8.6. 用户界面 - 申请退款 新建
class ApplyRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    public function rules(){
        return [
            'reason' => 'required',
        ];
    }
    // 8.6 添加
    public function attributes(){
        return [
            'reason' => '原因',
        ];
    }
}
