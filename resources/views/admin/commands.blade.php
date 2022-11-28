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
        <br/>
        接着，升级数据库（只需要在一台节点上执行）
        <code>art migrate</code>
        <br/>
        最后，清除缓存
        <code>art optimize</code>
        <br/>

        如果是非 Web 节点，需要重启全部或者对应服务，比如 队列，计划任务
        <code>supervisorctl restart all</code>

        <br/>
        如果是 Web 节点，需要简单重启即可
        <code>supervisorctl restart lae-web</code>
    </x-basic-card>

    <h5 class="mt-3">用户相关</h5>

    <h5 class="mt-3">应用程序</h5>
    <x-basic-card title="应用程序">
        <h5>要为外部程序服务，你需要先创建一个应用程序。</h5>
        应用程序的登录验证方式是 Bearer + Token。
    </x-basic-card>

    <h5 class="mt-3">MQTT</h5>
    <x-basic-card title="EMQX 认证配置">
        <h3>创建认证</h3>
        <p>在这之前，我们推荐你创建一个 Password-Based 的认证，选 Built-in Database ，账号类型选择 username。</p>
        <p>创建一个 HTTP Server 的数据源，请求方式为 POST。</p>

        URL 填 <strong>{{ route('applications.mqtt.authentication') }}</strong>
        <br/>
        Header 中增加一个 <strong>Authorization</strong>，值为 <strong>Bearer + 应用程序的密钥</strong>。注意空格。
        <br/>
        认证中的 Body 填写:
        <br/>
        <pre readonly>{
  "client_id": "${clientid}",
  "password": "${password}",
  "username": "${username}"
}
        </pre>
        <p>如果 EMQX 启用了 TLS，则你需要勾选 "TLS 配置" 下面的 "启用 TLS"，并且关闭 "验证服务器证书"。</p>
        之后，保存即可。接着，将你添加的认证设置放在 Built-in Database 下面。
    </x-basic-card>

    <x-basic-card title="EMQX 授权配置">
        <h3>创建授权</h3>
        <p>此操作将判断客户端是否有指定的权限。</p>

        <p>在这之前，我们推荐你创建一个 Built-in Database 的授权，之后关闭 File 授权。</p>
        <p>创建一个 Password-Based 的认证，服务选择 HTTP Server，请求方式为 POST。</p>

        URL 填 <strong>{{ route('applications.mqtt.authorization') }}</strong>
        <br/>
        Header 中增加一个 <strong>Authorization</strong>，值为 <strong>Bearer + 应用程序的密钥</strong>。注意空格。
        <br/>
        认证中的 Body 填写:
        <br/>
        <pre readonly>{
  "action": "${action}",
  "client_id": "${clientid}",
  "topic": "${topic}",
  "username": "${username}"
}
        </pre>
        <p>如果 EMQX 启用了 TLS，则你需要勾选 "TLS 配置" 下面的 "启用 TLS"，并且关闭 "验证服务器证书"。</p>
        之后，保存即可。接着，将你添加的认证设置放在 Built-in Database 之前（确保 Built-in Database 在 HTTP Server 下面）。

        <p>接下来，在授权页面，点击右上角的“设置”，将“未匹配时执行”调整为“deny”，然后保存。</p>

    </x-basic-card>

@endsection
