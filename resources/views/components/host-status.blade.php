<span>
    @switch($status)
        @case('running')
            <span class="badge bg-success">运行中</span>
            @break

        @case('active')
            <span class="badge bg-success">激活</span>
            @break

        @case('expired')
            <span class="badge bg-warning">到期</span>
            @break

        @case('suspended')
            <span class="badge bg-warning">已暂停</span>
            @break

        @case('stopped')
            <span class="badge bg-danger">已停止</span>
            @break

        @case('pending')
            <span class="badge bg-info">创建中</span>
            @break

        @case('draft')
            <span class="badge bg-secondary">草稿</span>
            @break

        @case('error')
            <span class="badge bg-danger">错误</span>
            @break

        @case('unavailable')
            <span class="badge bg-secondary">不可用</span>
            @break

        @case('locked')
            <span class="badge bg-danger">锁定</span>
            @break

        @case('one-time')
            <span class="badge bg-danger">一次性</span>
            @break

        @case('recurring')
            <span class="badge bg-success">循环</span>
            @break

        @case('free')
            <span class="badge bg-info">免费</span>
            @break

        @case('trial')
            <span class="badge bg-info">试用</span>
            @break

        @default
            <span class="badge bg-secondary">{{ $status }}</span>
    @endswitch
</span>
