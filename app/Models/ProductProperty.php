<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//6.1-new. 商品属性 新建
class ProductProperty extends Model
{
    protected $fillable = ['name', 'value'];
    // 没有 created_at 和 updated_at 字段
    public $timestamps = false;

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
