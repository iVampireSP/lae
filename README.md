# LaeCloud 莱云

### 主节点运行
```bash
docker run -itd --name=lae_schedule --restart=always -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art schedule:work

docker run -itd --name=lae_workers --restart=always -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art queue:work
```

### Web 节点运行
```bash
docker run -itd --name=lae --restart=always -p 8000:8000 -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art works
```
