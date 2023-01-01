<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use App\Policies\WorkOrder\ReplyPolicy;
use App\Policies\WorkOrder\WorkOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * 应用程序的策略映射。
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WorkOrder::class => WorkOrderPolicy::class,
        Reply::class => ReplyPolicy::class,
    ];

    /**
     * 注册任何应用程序 身份验证 / 授权服务。
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
