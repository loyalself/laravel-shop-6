<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// 3.5. 收货地址列表 新建
class UserAddress extends Model
{
    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
    ];
    /**
     * protected $dates = ['last_used_at']; 表示 last_used_at 字段是一个时间日期类型，在之后的代码中 $address->last_used_at 返回的就是一个时间日期对象（确切说是 Carbon 对象，Carbon 是 Laravel 默认使用的时间日期处理类）
     */
    protected $dates = ['last_used_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }
}
