<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductSku;
//6.4 购物车下单页面  新建
class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(){
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
            // 判断用户提交的地址 ID 是否存在于数据库并且属于当前用户
            // 后面这个条件非常重要，否则恶意用户可以用不同的地址 ID 不断提交订单来遍历出平台所有用户的收货地址
            'address_id'     => ['required', Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)],
            'items'          => ['required', 'array'],
            /**
             * 在检查 sku_id 时我们依然判断了对应 SKU 是否存在、商品是否上架、库存是否充足，因为用户在把商品加入购物车，再到下单时商品的各个状态都可能发生变化。
             *
             * 在检查库存时，我们需要获取用户想要购买的该 SKU 数量，我们可以通过匿名函数的第一个参数 $attribute 来获取当前 SKU 所在的数组索引，
             * 比如第一个 SKU 的 $attribute 就是 items.0.sku_id，所以我们采用正则的方式将这个 0 提取出来，$this->input('items')[0]['amount'] 就是用户想购买的数量.
             *
             */
            'items.*.sku_id' => [ // 检查 items 数组下每一个子数组的 sku_id 参数
                'required',
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::query()->find($value)) {
                        $fail('该商品不存在');
                        return;
                    }
                    if (!$sku->product->on_sale) {
                        $fail('该商品未上架');
                        return;
                    }
                    if ($sku->stock === 0) {
                        $fail('该商品已售完');
                        return;
                    }
                    // 获取当前索引
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                    $index  = $m[1];
                    // 根据索引找到用户所提交的购买数量
                    $amount = $this->input('items')[$index]['amount'];
                    if ($amount > 0 && $amount > $sku->stock) {
                        $fail('该商品库存不足');
                        return;
                    }
                },
            ],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
