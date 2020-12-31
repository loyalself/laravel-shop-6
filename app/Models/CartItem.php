<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//6.1 添加商品到购物车 新建
class CartItem extends Model
{
    protected $fillable = ['amount'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }
}
