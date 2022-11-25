<span>
    @switch($status)
        @case('running')
            <span class="badge bg-success">运行中</span>
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

        @case('error')
            <span class="badge bg-danger">错误</span>
            @break

        @default
            <span class="badge bg-secondary">{{ $status }}</span>
    @endswitch
</span>
