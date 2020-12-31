<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//3.1. 商品类目数据库结构设计 新建
class CreateCategoriesTable extends Migration
{
    /**
     * 这里我们需要解释一下 path 字段的意义，在无限级分类的实现中我们经常会遇到以下几个场景：
        场景一：查询一个类目的所有祖先类目，需要递归地去逐级查询父类目，会产生较多的 SQL 查询，从而影响性能。
        场景二：查询一个类目的所有后代类目，同样需要递归地逐级查询子类目，同样会产生很多 SQL 查询。
        场景三：判断两个类目是否有祖孙关系，需要从层级低的类目逐级往上查，性能低下。
     */
    public function up(){
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('类目名称');
            $table->unsignedInteger('parent_id')->nullable()->comment('父类目ID');
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->boolean('is_directory')->comment('是否拥有子类目');
            $table->unsignedInteger('level')->comment('当前类目层级');
            $table->string('path')->comment('该类目所有父类目 id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
