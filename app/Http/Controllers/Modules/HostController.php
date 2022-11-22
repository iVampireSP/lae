<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use function auth;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Paginator
     */
    public function index(Request $request): Paginator
    {
        return Host::where('module_id', $request->user('module')->id)->simplePaginate(100);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response|JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response|JsonResponse
    {
        // 存储计费项目
        $this->validate($request, [
            'status' => 'required|in:running,stopped,error,suspended,pending',
            'price' => 'required|numeric',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($request->price > 0) {
            if ($user->balance < 1) {
                return $this->error('此用户余额不足，无法开设计费项目。');
            }
        }

        // 如果没有 name，则随机
        $name = $request->input('name', Str::random(10));

        $data = [
            'name' => $name,
            'status' => $request->status,
            'price' => $request->price,
            'managed_price' => $request->managed_price,
            'user_id' => $user->id,
            'module_id' => auth('module')->id()
        ];

        $host = Host::create($data);

        $host['host_id'] = $host->id;

        return $this->created($host);
    }

    /**
     * Display the specified resource.
     *
     * @param Host $host
     *
     * @return JsonResponse
     */
    public function show(Host $host): JsonResponse
    {

        return $this->success($host);
        //

        // dd($host->cost());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Host    $host
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Host $host): JsonResponse
    {
        //
        $this->validate($request, [
            'status' => 'sometimes|in:running,stopped,error,suspended,pending',
            'managed_price' => 'sometimes|numeric|nullable',

            // 如果是立即扣费
            'cost_once' => 'sometimes|numeric|nullable',
        ]);

        // if has cost_once
        if ($request->has('cost_once')) {
            $host->cost($request->cost_once ?? 0, false);

            return $this->updated();
        }

        $update = $request->except(['module_id', 'user_id']);

        $host->update($update);

        return $this->updated($host);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $host
     *
     * @return JsonResponse
     */
    public function destroy($host): JsonResponse
    {
        // if host not instance of Host
        if (!$host instanceof Host) {
            $host = Host::findOrFail($host);
        }

        $host?->delete();

        return $this->deleted();
    }
}
