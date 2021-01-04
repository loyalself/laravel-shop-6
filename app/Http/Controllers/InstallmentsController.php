<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;
//5.3-new. 创建分期付款 新建:
class InstallmentsController extends Controller
{
    public function index(Request $request){
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);
        return view('installments.index', ['installments' => $installments]);
    }
}
