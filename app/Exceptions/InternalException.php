<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

/**
 * 3.4. 优雅地处理异常 新建:
 */
class InternalException extends Exception
{
    protected $msgForUser;

    /**
     * 这个异常的构造函数第一个参数就是原本应该有的异常信息比如连接数据库失败,第二个参数是展示给用户的信息,通常来说只需要告诉用户 系统内部错误 即可,
     * 因为不管是连接 Mysql 失败还是连接 Redis 失败对用户来说都是一样的，就是系统不可用，用户也不可能根据这个信息来解决什么问题
     */
    public function __construct(string $message, string $msgForUser = '系统内部错误', int $code = 500){
        parent::__construct($message, $code);
        $this->msgForUser = $msgForUser;
    }

    public function render(Request $request){
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->msgForUser], $this->code);
        }
        return view('pages.error', ['msg' => $this->msgForUser]);
    }
}
