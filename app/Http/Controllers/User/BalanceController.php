<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    //

    public function index(Request $request)
    {
        //
        $balance = $request->user();
        return $this->success($balance);
    }

    public function store(Request $request)
    {
      
        // 充值
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $balance = $request->user();

        // 启用事物
        \DB::beginTransaction();
        try {
            $balance->increment('amount', $request->amount);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->error($e->getMessage());
        }

        return $this->success($balance);
    }


    // // 转换为 drops
    // public function transfer($amount = 1)
    // {
    //     $balance = auth('sanctum')->user();
    //     $balance->decrement('amount', $request->amount);
    //     return $this->success($balance);
    // }




}
