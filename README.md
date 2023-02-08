# LaeCloud 莱云

### 扣费队列
1. default 默认扣费队列
2. host-cost 机器扣费队列

### 主节点运行
```bash
docker run -itd --name=lae_schedule --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art schedule:work

docker run -itd --name=lae_worker --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art queue:work --queue=default,host-cost
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
