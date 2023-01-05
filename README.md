# LaeCloud 莱云

### 主节点运行
```bash
docker run -itd --name=lae_schedule --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art schedule:work

docker run -itd --name=lae_workers --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art queue:work
```

### Web 节点运行
```bash
docker run -itd --name=lae --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art works
```


### 单次执行 比如 migrate 或者 composer
```bash
docker run --rm --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte php
```
```bash
docker run --rm --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art
```
