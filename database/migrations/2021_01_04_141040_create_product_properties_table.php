<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//6.1-new. 商品属性  新建:
class CreateProductPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->comment('商品id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('name')->comment('属性名称');
            $table->string('value')->comment('属性值');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_properties');
    }
}
