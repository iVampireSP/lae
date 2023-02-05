<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\User\UserNotification;
use App\Support\RealNameSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RealNameController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $result = (new RealNameSupport())->verify($request->all());

        if (!$result) {
            Log::warning('实名认证失败', $request->all());

            return $this->error('实名认证失败。');
        }

        $user = (new User)->find($result['user_id']);
        $user->real_name = $result['name'];
        $user->id_card = $result['id_card'];
        $user->save();

        $user->reduce((string)config('settings.supports.real_name.price'), '实名认证费用。');

        $user->notify(new UserNotification('再次欢迎您！', '再次欢迎您！您的实人认证已通过。', true));

        return $this->success('实名认证成功。');
    }

    public function process(): View
    {
        return view('real_name.process');
    }
}
