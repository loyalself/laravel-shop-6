<?php
namespace App\Models;
use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

//9.1. 管理后台 - 优惠券列表 新建
class CouponCode extends Model
{
    // 用常量的方式定义支持的优惠券类型
    const TYPE_FIXED   = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED   => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];
    protected $casts = [
        'enabled' => 'boolean',
    ];
    // 指明这两个字段是日期类型
    protected $dates = ['not_before', 'not_after'];

    // 优惠码生成
    public static function findAvailableCode($length = 16){
        do {
            // 生成一个指定长度的随机字符串，并转成大写
            $code = strtoupper(Str::random($length));
            // 如果生成的码已存在就继续循环
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    protected $appends = ['description'];

    public function getDescriptionAttribute(){
        $str = '';
        if ($this->min_amount > 0) {
            $str = '满'.str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str.'优惠'.str_replace('.00', '', $this->value).'%';
        }
        return $str.'减'.str_replace('.00', '', $this->value);
    }

    /**
     * 9.4. 用户界面 - 使用优惠券下单 添加: 检测优惠券是否有效
     * 1.虽然我们在提交订单之前就已经检查过一次优惠码，但是提交时需要再次检查，因为有可能在用户检查优惠码和提交的时间空档中优惠券被其他人兑完了，或者是运营人员修改了优惠码规则;
     * 2.这个 checkAvailable() 方法接受一个参数 $orderAmount 订单金额。为了兼容用户下单前的校验，如果传入的 $orderAmount 是 null 则不去检查是否满足订单最低金额.
     */
    //public function checkAvailable($orderAmount = null){
    public function checkAvailable(User $user,$orderAmount = null){ //9.5 添加 $user
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }
        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }
        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }
        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }
        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }

        /**
         * 9.5 添加: 这里的构造器最终生成的sql类似:
         * select * from orders where user_id = xx and coupon_code_id = xx
            and (
                    ( paid_at is null and closed = 0 ) or ( paid_at is not null and refund_status = 'pending' )
            )
         *
         * 代码中使用 where(function($query) {}) 嵌套是用来生成的 SQL 里的括号，保证不会因为 or 关键字导致我们的查出来的结果与期望不符。
         *
         *
         */
        $used = Order::query()->where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNull('paid_at')->where('closed', false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')->where('refund_status', Order::REFUND_STATUS_PENDING);
                });
            })
            ->exists();
        if ($used) {
            throw new CouponCodeUnavailableException('你已经使用过这张优惠券了');
        }
    }




    /**
     * 9.4 添加: 计算使用优惠券优惠后金额
     */
    public function getAdjustedPrice($orderAmount){
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->value);
        }
        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    /**
     * 9.4 添加:
     * 优惠券的用量和商品 SKU 的库存类似，用户下单时我们新增对应优惠券的用量，如果订单超时关闭则减少用量
     */
    public function changeUsed($increase = true){
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
