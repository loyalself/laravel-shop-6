<?php

namespace App\Http\Controllers;

use App\Exceptions\CouponCodeUnavailableException;
use App\Models\CouponCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
//9.3. 用户界面 - 检查优惠券 新建
class CouponCodesController extends Controller
{
    /**
     * 其中 abort() 方法可以直接中断我们程序的运行，接受的参数会变成 Http 状态码返回。
     * 在这里如果用户输入的优惠码不存在或者是没有启用我们就返回 404 给用户。
     */
    //public function show($code)
    public function show($code,Request $request) //9.5 优化优惠券模块 添加: $request
    {
        // 判断优惠券是否存在
        /*if (!$record = CouponCode::query()->where('code', $code)->first()) {
            abort(404);
        }

        // 如果优惠券没有启用，则等同于优惠券不存在
        if (!$record->enabled) {
            abort(404);
        }

        if ($record->total - $record->used <= 0) {
            return response()->json(['msg' => '该优惠券已被兑完'], 403);
        }

        if ($record->not_before && $record->not_before->gt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券现在还不能使用'], 403);
        }

        if ($record->not_after && $record->not_after->lt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券已过期'], 403);
        }*/

        //9.4. 用户界面 - 使用优惠券下单 修改
        if (!$record = CouponCode::query()->where('code', $code)->first()) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }
        //$record->checkAvailable();

        //9.5  修改:
        $record->checkAvailable($request->user());

        return $record;
    }
}
