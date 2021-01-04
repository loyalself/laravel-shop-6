<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Moontoast\Math\BigNumber;

//5.2-new. 分期付款数据库结构设计 新建
class InstallmentItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
    ];

    protected $fillable = [
        'sequence',
        'base',
        'fee',
        'fine',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
    ];
    protected $dates = ['due_date', 'paid_at'];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    // 创建一个访问器，返回当前还款计划需还款的总金额
    public function getTotalAttribute(){
        /**
         * 小数点计算需要用 bcmath 扩展提供的函数:
         * 我们在代码中使用了 PHP 的官方扩展 bcmath 提供的函数来进行金额计算，这是为了避免浮点数运算不精确的问题。
         * 但是 bcmath 函数用起来很不方便，我们通常会使用 moontoast/math 这个库来作为替代，这个库的底层也是依赖于 bcmath，主要是做了面向对象的封装。
         */
       /* $total = bcadd($this->base, $this->fee, 2);
        if (!is_null($this->fine)) {
            $total = bcadd($total, $this->fine, 2);
        }
        return $total;
       */

        /**
         * 但是每次使用都要 new 一次对象并且设定精度，还是比较麻烦的，我们可以在 helpers.php 里创建一个辅助函数来方便我们调用
         */
        //$total = (new BigNumber($this->base, 2))->add($this->fee);
        $total = big_number($this->base)->add($this->fee);
        if (!is_null($this->fine)) {
            $total->add($this->fine);
        }
        return $total->getValue();
    }

    // 创建一个访问器，返回当前还款计划是否已经逾期
    public function getIsOverdueAttribute()
    {
        return Carbon::now()->gt($this->due_date);
    }
}
