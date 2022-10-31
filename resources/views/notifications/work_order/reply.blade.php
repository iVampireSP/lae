## @if ($workOrder->is_pending)
    回复投递中
@else
    回复已投递
@endif

# {{ $module->name }}

## 客户 {{ $user->name }}
##### 邮箱 {{ $user->email }}
##### 余额 {{ $user->balance }} 元

# {{ $workOrder->id }}#{{ $workOrder->title }}

{{ $reply->content }}
