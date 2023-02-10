<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use function auth;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// use App\Models\User;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Paginator
    {
        return (new Host)->where('module_id', $request->user('module')->id)->simplePaginate(100);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response|JsonResponse
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response|JsonResponse
    {
        // 存储计费项目
        $this->validate($request, [
            'status' => 'required|in:running,stopped,error,suspended,pending',
            'price' => 'required|numeric',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = (new User)->findOrFail($request->input('user_id'));

        if ($request->input('price') > 0) {
            if ($user->balance < 1) {
                return $this->error('此用户余额不足，无法开设计费项目。');
            }
        }

        // 如果没有 name，则随机
        $name = $request->input('name', Str::random(10));

        $data = [
            'name' => $name,
            'status' => $request->input('status'),
            'price' => $request->input('price'),
            'managed_price' => $request->input('managed_price'),
            'user_id' => $user->id,
            'module_id' => auth('module')->id(),
        ];

        $host = (new Host)->create($data);

        $host['host_id'] = $host->id;

        return $this->created($host);
    }

    /**
     * Display the specified resource.
     *
     * @param  Host  $host
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
     * @param  Request  $request
     * @param  Host  $host
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function update(Request $request, Host $host): JsonResponse
    {
        $this->validate($request, [
            'status' => 'sometimes|in:running,stopped,error,suspended,pending',
            'managed_price' => 'sometimes|numeric|nullable',
        ]);

        $update = $request->except(['module_id', 'user_id']);

        $host->update($update);

        return $this->updated($host);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    $host
     * @return JsonResponse
     */
    public function destroy($host): JsonResponse
    {
        // if host not instance of HostJob
        if (! $host instanceof Host) {
            $host = (new Host)->findOrFail($host);
        }

        $host?->delete();

        return $this->deleted();
    }

    public function cost(Request $request, Host $host): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string:max:255',
        ]);

        $host->cost($request->input('amount'), false);

        return $this->noContent();
    }
}
