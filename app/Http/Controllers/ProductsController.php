<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
// 5.4-old. 商品列表页面 新建
class ProductsController extends Controller
{
    /**
     * 商品列表页
     */
    public function index(Request $request){
        //$products = Product::query()->where('on_sale', true)->paginate();
        // 5.4-old. 同章修改
        /*$products = Product::query()->where('on_sale', true)->paginate(16);
        return view('products.index', ['products' => $products]);*/

        //5.5-old. 商品筛选、排序 修改
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        // 3.5. 完善前台商品列表页 添加:如果有传入 category_id 字段，并且在数据库中有对应的类目
        if ($request->input('category_id') && $category = Category::query()->find($request->input('category_id'))) {
            // 如果这是一个父类目
            if ($category->is_directory) {
                // 则筛选出该父类目下所有子类目的商品
                $builder->whereHas('category', function ($query) use ($category) {
                    // 这里的逻辑参考本章第一节
                    $query->where('path', 'like', $category->path.$category->id.'-%');
                });
            } else {
                // 如果这不是一个父类目，则直接筛选此类目下的商品
                $builder->where('category_id', $category->id);
            }
        }
        $products = $builder->paginate(16);
        //return view('products.index', ['products' => $products]);
        return view('products.index', [
            'products' => $products,
            'category' => $category ?? null,  // 3.5 添加: 等价于 isset($category) ? $category : null
            'filters'  => [
                'search'   => $search,
                'order'    => $order
            ],
        ]);
    }
    /**
     * 5.6-old. 商品详情页 添加:
     */
    public function show(Product $product, Request $request){
        // 判断商品是否已经上架，如果没有上架则抛出异常。
       /* if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }
        return view('products.show', ['product' => $product]);*/

        // 5.7-old 修改
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }
        $favored = false;
        // 用户未登录时返回的是 null，已登录时返回的是对应的用户对象
        if($user = $request->user()) {
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            // boolval() 函数用于把值转为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }
        //8.5-old 获取商品评价数据 添加:
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        //return view('products.show', ['product' => $product, 'favored' => $favored]);
        //8.5-old 修改
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews
        ]);

    }
    /**
     * 5.7-old. 收藏商品 添加:
     * 1.这段代码先是判断当前用户是否已经收藏了此商品，如果已经收藏则不做任何操作直接返回，否则通过 attach() 方法将当前用户和此商品关联起来;
     * 2.attach() 方法的参数可以是模型的 id，也可以是模型对象本身，因此这里还可以写成 attach($product->id).
     */
    public function favor(Product $product, Request $request){
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }
    /**
     * 5.7-old. 取消收藏商品:
     * detach() 方法用于取消多对多的关联，接受的参数个数与 attach() 方法一致
     */
    public function disfavor(Product $product, Request $request){
        $user = $request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }

    //5.7-old 收藏商品列表
    public function favorites(Request $request){
        //这里我们用分页的方式取出当前用户的收藏商品，由于我们在定义关联关系的时候就已经加上了排序规则，这里就不需要再次设置了.
        $products = $request->user()->favoriteProducts()->paginate(16);
        return view('products.favorites', ['products' => $products]);
    }
}
