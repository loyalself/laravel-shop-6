<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * 2.6 基础布局 添加: 首页
     */
    public function root(){
        return view('pages.root');
    }

    /**
     * 3.2 验证邮箱(上) 添加:
     */
    public function emailVerifyNotice(Request $request){
        return view('pages.email_verify_notice');
    }
}
