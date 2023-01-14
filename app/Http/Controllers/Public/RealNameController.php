<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Support\RealNameSupport;
use Illuminate\Http\Request;

class RealNameController extends Controller
{
    public function verify(Request $request)
    {
        $result = (new RealNameSupport())->verify($request->all());

        if (!$result) {
            return $this->error('实名认证失败。');
        }

        $user = User::find($result['user_id']);
        $user->real_name = $result['name'];
        $user->id_card = $result['id_card'];
        $user->save();

        $transaction = new Transaction();
        $transaction->reduceAmount($user->id, 0.7, '实名认证费用。');

        return $this->success('实名认证成功。');
    }

    public function process()
    {
        return view('real_name.process');
    }
}
