<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\User\UserNotification;
use App\Support\RealNameSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RealNameController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        Log::debug('实名认证回调', $request->all());

        return $this->validateOrSave($request)
            ? $this->success()
            : $this->failed();
    }

    public function process(Request $request): View
    {
        Log::debug('实名认证回调', $request->all());

        return $this->validateOrSave($request)
            ? view('real_name.success')
            : view('real_name.failed');
    }

    public function validateOrSave(Request $request): bool
    {
        Log::debug('实名认证回调', $request->all());

        $result = (new RealNameSupport())->verify($request->all());

        if (! $result) {
            Log::warning('实名认证失败', $request->all());

            return false;
        }

        Cache::lock('user_realname', 60)->get(function ()use ($result) {
            $user = (new User)->find($result['user_id']);

            if ($user->real_name_verified_at) {
                return false;
            }

            $user->real_name = $result['name'];
            $user->id_card = $result['id_card'];
            $user->save();

            $user->reduce((string) config('settings.supports.real_name.price'), '实名认证费用。');

            $user->notify(new UserNotification('再次欢迎您！', '再次欢迎您！您的实人认证已通过。'));

            return true;
        });

        return true;
    }
}
