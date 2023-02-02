<span>
    @switch($status)
        @case('pending')
            <span class="badge bg-info">推送中</span>
            @break

        @case('open')
            <span class="badge bg-success">开启</span>
            @break

        @case('user_read')
            <span class="badge bg-success">用户已读</span>
            @break

        @case('user_replied')
            <span class="badge bg-warning">用户已回复</span>
            @break

        @case('replied')
            <span class="badge bg-success">已回复</span>
            @break

        @case('read')
            <span class="badge bg-success">已读</span>
            @break

        @case('on_hold')
            <span class="badge bg-secondary">挂起</span>
            @break

        @case('in_progress')
            <span class="badge bg-secondary">处理中</span>
            @break

        @case('closed')
            <span class="badge bg-secondary">关闭</span>
            @break

        @case('error')
            <span class="badge bg-danger">错误</span>
            @break

        @default
            <span class="badge bg-secondary">{{ $status }}</span>
    @endswitch
</span>
