<?php
namespace App\Http\Controllers;
use App\Http\Requests\AddCartRequest;
use App\Models\ProductSku;
use App\Services\CartService;
use Illuminate\Http\Request;
//6.8. 封装业务代码 修改
class CartController extends Controller
{
    protected $cartService;

    /**
     * 利用 Laravel 的自动解析功能注入 CartService 类:
     * 这里我们使用了 Laravel 容器的自动解析功能，当 Laravel 初始化 Controller 类时会检查该类的构造函数参数，在本例中 Laravel 会自动创建一个 CartService 对象作为构造参数传入给 CartController。
     */
    public function __construct(CartService $cartService){
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cartItems = $this->cartService->get();
        $addresses = $request->user()->addresses()->orderBy('last_used_at', 'desc')->get();

        return view('cart.index', ['cartItems' => $cartItems, 'addresses' => $addresses]);
    }

    public function add(AddCartRequest $request)
    {
        $this->cartService->add($request->input('sku_id'), $request->input('amount'));

        return [];
    }

    public function remove(ProductSku $sku, Request $request)
    {
        $this->cartService->remove($sku->id);

        return [];
    }
}