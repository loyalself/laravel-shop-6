<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//4.2. 众筹商品数据库结构设计 新建
class CreateCrowdfundingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crowdfunding_products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->comment('与商品一对一关联');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->decimal('target_amount', 10, 2)->comment('众筹目标金额');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('当前已筹金额');
            $table->unsignedInteger('user_count')->default(0)->comment('当前参与众筹用户数');
            $table->dateTime('end_at')->comment('众筹结束时间');
            $table->string('status')->comment('当前筹款的状态');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crowdfunding_products');
    }
}
