<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\Request;
//3.5. 收货地址列表 新建
class UserAddressesController extends Controller
{
    /**
     * 3.5 收获地址列表
     */
    public function index(Request $request){
        return view('user_addresses.index', [
            'addresses' => $request->user()->addresses,
        ]);
    }
    // 3.6. 新建收货地址 添加
    public function create(){
        return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }

    /**
     * 3.6. 新建收货地址 添加: 新增收获地址逻辑
     * 1.Laravel 会自动调用 UserAddressRequest 中的 rules() 方法获取校验规则来对用户提交的数据进行校验，因此这里我们不需要手动调用 $this->validate() 方法。
       2.$request->user() 获取当前登录用户。
       3.user()->addresses() 获取当前用户与地址的关联关系（注意：这里并不是获取当前用户的地址列表）
       4.addresses()->create() 在关联关系里创建一个新的记录。
       5.$request->only() 通过白名单的方式从用户提交的数据里获取我们所需要的数据。
       6.return redirect()->route('user_addresses.index'); 跳转回我们的地址列表页面
     */
    public function store(UserAddressRequest $request){
        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));

        return redirect()->route('user_addresses.index');
    }

    // 3.7. 修改和删除收货地址 添加: 修改收货地址页面
    public function edit(UserAddress $user_address){
        /**
         * authorize('own', $user_address) 方法会获取第二个参数 $user_address 的类名: App\Models\UserAddress，
         * 然后在 AuthServiceProvider 类的 $policies 属性中寻找对应的策略，在这里就是 App\Policies\UserAddressPolicy，
         * 找到之后会实例化这个策略类，再调用名为 own() 方法，如果 own() 方法返回 false 则会抛出一个未授权的异常.
         */
        $this->authorize('own', $user_address);  // 3.7 添加
        return view('user_addresses.create_and_edit', ['address' => $user_address]);
    }
    // 3.7 添加:修改收货地址逻辑
    public function update(UserAddress $user_address, UserAddressRequest $request){
        $this->authorize('own', $user_address); // 3.7 添加
        $user_address->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return redirect()->route('user_addresses.index');
    }
    // 3.7 添加:删除收货地址
    public function destroy(UserAddress $user_address){
        $this->authorize('own', $user_address); // 3.7 添加
        $user_address->delete();
        //return redirect()->route('user_addresses.index');
        return []; //  3.7 同章修改
    }
}
