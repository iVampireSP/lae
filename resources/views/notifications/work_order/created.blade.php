@php
    $title = '';
@endphp

@switch ($workOrder->status)
    @case('pending')
    @php
        $title = '工单挂起';
    @endphp
    @break

    @case('open')
    @php
        $title = '工单投递成功，并且已开启';
    @endphp
    @break

    @case('user_replied')
    @php
        $title = '工单挂起';
    @endphp
    @break

    @case('closed')
    @php
        $title = '已结单';
    @endphp
    @break

    @case('replied')
    @php
        $title = '工作人员已回复';
    @endphp
    @break

    @case('on_hold')
    @php
        $title = '挂起';
    @endphp
    @break

    @case('in_progress')
    @php
        $title = '正在处理中';
    @endphp
    @break

    @case('error')
    @php
        $title = '投递失败';
    @endphp
    @break

    @default
    @php
        $title = '状态更新';
    @endphp
    @break
@endswitch

## {{ $title }}

# {{ $module->name }}

## 客户 {{ $user->name }}
##### 邮箱 {{ $user->email }}
##### 余额 {{ $user->balance }} 元

# {{ $workOrder->id }}#{{ $workOrder->title }}

{{ $workOrder->content }}
