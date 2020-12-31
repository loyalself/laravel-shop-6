<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
//8.7. 管理后台 - 拒绝退款 新建:
class HandleRefundRequest extends FormRequest
{
    public function authorize(){
        //return false;
        return true;
    }

    public function rules(){
        return [
            'agree'  => ['required', 'boolean'],
            'reason' => ['required_if:agree,false'], // 拒绝退款时需要输入拒绝理由
        ];
    }
}
