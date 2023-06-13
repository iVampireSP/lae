# LaeCloud 莱云 v3

### 这是第三代莱云的系统
它具有 天生分布式，可集群化，方便部署，高性能 等特点。美中不足的是，它的周期计费做的不是很好。

(在 laecloud.com 上查看 莱云 v3)[https://www.laecloud.com/lae_v3/]

功能列表
用户端
1. API Token 管理
2. 主机管理
3. 财务管理
4. 转账功能
5. 推介计划
6. 维护计划

管理员
1. 用户管理
2. 模块管理
3. 通知广播（支持前端实时通知）
4. 主机管理
5. MQTT 在线设备管理（需要 EMQX）
6. 外置应用管理
7. 集群节点调度管理，以及集群操作。
8. 命令行集群日志
9. 工单
10. 维护计划和计划维护
11. 用户组



### 模块化功能
莱云 v3 具有良好的拓展性，它以模块区分功能，每个模块都是一套独立的系统，尽可能减少代码冗余。
我们甚至预留了 MQTT 鉴权模块。

### 标准的 RESTful
莱云 v3 的 API 设计完全符合 RESTful API 的标准。

### 易于部署
我们吸取了[第一代](https://github.com/loliart-lae/lae_v1)的教训，本代产品支持容器化部署和扩容。

### 前端 用户界面
(lae-ui)[https://github.com/iVampireSP/lae-ui]

### 模块的功能
[Module Template](https://github.com/iVampireSP/lae-example-module)

- 用户管理（从主站发现的用户）
- 主机管理（管理用户创建的服务）
- 收益报表（从主站获取）
- 通知发送（支持前端实时弹出通知）
- MQTT 设备管理 / 物联网设备管理

### 一些模块

#### 第三代(lae v3)
[Module Template](https://github.com/iVampireSP/lae-example-module)
[Pterodactyl 自动化](https://github.com/iVampireSP/lae-module-pterodactyl)
[服务于用户的 MQTT](https://github.com/iVampireSP/lae-module-user-mqtt)
[IP Manager](https://github.com/iVampireSP/lae-ip-manager)
[ForbiddenForest](https://github.com/iVampireSP/lae-forbiddenforest)
[拼手气红包模块](https://github.com/iVampireSP/lae-redpacket)

#### 外置应用
[模块网关](https://github.com/iVampireSP/lae-gateway)

#### 第一代(lae_v1)
[lae_v1](https://github.com/orgs/loliart-lae/repositories) 主程序

[Frp Tunnel Client](https://github.com/loliart-lae/lae-tunnel-client)
[cyberpanelManager](https://github.com/loliart-lae/lm-cyberpanelManager)
[共享的远程桌面自动化](https://github.com/loliart-lae/lae-windows-agent)
[FTP 服务器](https://github.com/loliart-lae/lae-ftp-server)
[静态站点 Agent](https://github.com/loliart-lae/lae-staticSite-agent)


### 其他项目

(PortIO 基于 ME Frp)[https://github.com/LaeCloud/PortIO]
(PortIO 概念)[https://www.laecloud.com/portio-concept/]

### WHMCS 项目
(共享的 Windows 远程桌面自动化)[https://www.laecloud.com/shared-desktop-automatic/]
(优化后的 Pterodactyl WHMCS 模块)[https://www.laecloud.com/optimized-pterodactyl-whmcs-module/]
(Pterodactyl 面板预设和 LiteLoaderBDS 支持)[https://www.laecloud.com/pterodactyl-cn/]
(WHMCS + ChatGPT 工单助理)[https://www.laecloud.com/whmcs-gpt-%e5%b7%a5%e5%8d%95%e5%8a%a9%e7%90%86/]


### 为何会停止开发
此项目作为我一人维护的项目，已经没有多大动力了。
虽然它的理念很好，但是打理起来，是个很麻烦的事情。
虽然团队人不少，但是无人与我志同道合。
我当时对此项目的评价，自我认它是一个艺术品。它确实有很多不足，并且也称不上太过完美/完善。并且与其留着，不如放出来供大家欣赏借鉴。

关于转 WHMCS。我将会继续干我想干的事情，我们的团队将会服务好每一个客户。
我将在干这个的同时，继续寻找下一个目标与道友。

### 我可以二次开发吗？
当然可以，本仓库使用 MIT 协议。您可以干您任何想做的事情。


## 部署方式

克隆此仓库，随后根据 Laravel 的方式安装。


## 运行方式

Tips: 优雅的命令行
```bash
alias art="php artisan"
```

1. 使用 Nginx + PHP-FPM
2. 使用 art works 命令。
3. 使用以下方法在 Docker 中部署。

### 扣费队列
1. default 默认扣费队列
2. host-cost 机器扣费队列

### 主节点运行
```bash
docker run -itd --name=lae_schedule --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art schedule:work

docker run -itd --name=lae_worker --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art queue:work --queue=default,host-cost,notifications
```

### Web 节点运行
```bash
docker run -itd --name=lae --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art works
```


### 单次执行 比如 migrate 或者 composer
```bash
docker run -it --rm --init --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte php
```
```bash
docker run -it --rm --init --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art
```
