<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price',
        'type', //4.2-new. 众筹商品数据库结构设计 添加
        'title',       //6.2-new. 商品长标题 添加
        'long_title', //6.2-new 添加
    ];
    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];
    // 与商品SKU关联,一对多,一个商品可能会有多个 SKU
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function getImageUrlAttribute(){
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        // \Storage::disk('public') 的参数 public 需要和我们在 config/admin.php 里面的 upload.disk 配置一致。
        return \Storage::disk('public')->url($this->attributes['image']);
    }

    //3.1-new. 商品类目数据库结构设计 添加:
    public function category(){
        return $this->belongsTo(Category::class);
    }
    // 4.2-new. 众筹商品数据库结构设计-new 添加:
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';

    public static $typeMap = [
        self::TYPE_NORMAL  => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
    ];

    public function crowdfunding(){
        return $this->hasOne(CrowdfundingProduct::class);
    }
    //一个商品可以有很多个属性
    public function properties(){
        return $this->hasMany(ProductProperty::class);
    }

    //6.1-new. 商品属性 添加:
    public function getGroupedPropertiesAttribute(){
        /**
         * 代码解析:
         * $this->properties 获取当前商品的商品属性集合（一个 Collection 对象）;
         * ->groupBy('name') 是集合的方法，得到的结果;
         * ->map(function() { xxx }) 会遍历上述数组的每一项的值，把值作为参数传递给我们的回调函数，然后把回调函数的返回值重新组成一个新的集合;
         * 在回调函数里我们调用了集合的 pluck('name') 方法，这个方法会返回该集合中所有的 name 字段值所组成的新集合.
         */
        return $this->properties
            // 按照属性名聚合，返回的集合的 key 是属性名，value 是包含该属性名的所有属性集合
            ->groupBy('name')
            ->map(function ($properties) {
                // 使用 map 方法将属性集合变为属性值集合
                return $properties->pluck('value')->all();
            });
    }
}
