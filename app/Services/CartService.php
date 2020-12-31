<?php

namespace App\Services;

use Auth;
use App\Models\CartItem;
//6.8. 封装业务代码 新建
class CartService
{
    public function get(){
        /**
         * 这里直接通过 Auth::user() 的方式获取用户，这是因为通常来说只有当前用户才会操作自己的购物车，所以可以不需要从外部传入 $user 对象
         */
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add($skuId, $amount)
    {
        $user = Auth::user();
        // 从数据库中查询该商品是否已经在购物车中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            // 否则创建一个新的购物车记录
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    /**
     * remove() 方法的参数可以传入单个 ID 也能传入一个 ID 数组，这个是 Laravel 中很常见的设计，比如 Model 对象里面的 with()、load() 等方法都是支持只传一个值和一个数组的，这样调用起来会十分方便
     */
    public function remove($skuIds){
        // 可以传单个 ID，也可以传 ID 数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}