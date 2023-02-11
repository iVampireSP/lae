<span>
    @switch($status)
        @case('up')
            <span class="badge bg-success">正常</span>
            @break

        @case('maintenance')
            <span class="badge bg-warning">维护</span>
            @break

        @case('down')
            <span class="badge bg-danger">异常</span>
            @break

        @default
            <span class="badge bg-secondary">{{ $status }}</span>
    @endswitch
</span>
