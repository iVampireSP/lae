@php use Illuminate\Support\Carbon; @endphp
@extends('layouts.admin')

@section('title', '监视器')

@section('content')
    <h3>集群日志监视器</h3>

    <div id="logs"></div>

    <script>
        const eventSource = new EventSource('stream');

        let line = 0;
        let currentLine = 0;
        let maxLine = 100;

        const logs = document.getElementById('logs');

        eventSource.onmessage = function (event) {
            const text = event.data

            if (text === '') {
                return;
            }

            // 将 text 填充到 logs 中，如果超过 maxLine 行，则删除最早的一行
            const lines = text.split("\n");
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];
                if (line === '') {
                    continue;
                }

                const span = document.createElement('span');
                const localDateTimeString = new Date().toLocaleString();
                span.innerHTML = `[${localDateTimeString}]` + line + '<br/>';
                logs.appendChild(span);

                if (currentLine > maxLine) {
                    logs.removeChild(logs.firstChild);
                    currentLine--;
                }

                currentLine++;

                window.scrollTo(0, document.body.scrollHeight);
            }
        }

    </script>

@endsection
