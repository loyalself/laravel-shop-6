<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 4.6. 订单模块调整 新建:
 * 我们先来回顾一下众筹的两个业务逻辑：
   1.众筹订单不支持用户主动申请退款；
   2.在众筹成功之前订单不可发货。

 * 可见当订单是一个众筹商品订单时，我们的系统会有与普通订单不同的处理逻辑。
  在现有的数据库结构下，要实现这些逻辑时只能通过判断订单下 SKU 对应商品是否众筹商品才能确定是否是一个众筹商品订单，这样不仅实现繁琐，还会有不少额外的 SQL 查询。
  为了解决这个问题，我们可以在订单表中添加一个 type 字段来表明这个订单是普通商品订单还是众筹商品订单.
 */
class OrdersAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type')->after('id')->default(\App\Models\Order::TYPE_NORMAL);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
