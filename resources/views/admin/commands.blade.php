@extends('layouts.admin')

@section('title', '命令速查表')

@section('content')
    <h3>命令速查表</h3>

    <h5>系统维护</h5>
    <x-basic-card title="启动 莱云">
        启动全部
        <code>supervistorctl start all</code>
        <br/>
        启动 Web 节点
        <code>supervistorctl start lae-web</code>
        或者
        <code>art octane:start</code>
        <br/>
        启动 队列
        <code>supervistorctl start lae-queue</code>
        <br/>
        启动 计划任务
        <code>supervistorctl start lae-schedule</code>
    </x-basic-card>

    <x-basic-card title="停止 莱云">
        停止全部
        <code>supervistorctl stop all</code>
        <br/>
        停止 Web 节点
        <code>supervistorctl stop lae-web</code>
        或者
        <code>art octane:stop</code>
        <br/>
        停止 队列
        <code>supervistorctl stop lae-queue</code>
        <br/>
        停止 计划任务
        <code>supervistorctl stop lae-schedule</code>
    </x-basic-card>

    <x-basic-card title="重启 莱云">
        重启全部
        <code>supervistorctl restart all</code>
        <br/>
        重启 Web 节点
        <code>supervistorctl restart lae-web</code>
        或者
        <code>art octane:restart</code>
        *重载<code>art octane:reload</code>
        <br/>
        重启 队列
        <code>supervistorctl restart lae-queue</code>
        <br/>
        重启 计划任务
        <code>supervistorctl restart lae-schedule</code>
    </x-basic-card>

    <x-basic-card title="基本维护">
        启动维护模式
        <code>art down</code>
        <br/>
        关闭维护模式
        <code>art up</code>

    </x-basic-card>


    <x-basic-card title="升级">
        <p class="text-danger">*如果是大更新，需要启动维护模式。</p>
        首先，拉取最新代码
        <code>sudo -u www git pull</code>
        <br/>
        然后，安装依赖
        <code>composer install --no-dev</code>
        <br />
        接着，升级数据库（只需要在一台节点上执行）
        <code>art migrate</code>
        <br />
        最后，清除缓存
        <code>art optimize</code>
        <br />

        如果是非 Web 节点，需要重启全部或者对应服务，比如 队列，计划任务
        <code>supervisorctl restart all</code>

        <br />
        如果是 Web 节点，需要简单重启即可
        <code>supervisorctl restart lae-web</code>

    </x-basic-card>




    <h5 class="mt-3">用户相关</h5>




@endsection
