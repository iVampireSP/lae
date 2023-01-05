# LaeCloud 莱云

### 主节点运行
复制 supervisor/ 下的所有文件到 /etc/supervisor/conf.d 中，然后全部启动

### Web 节点运行
```bash
docker run -itd --name=lae --restart=always -p 8000:8000 -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art works\
```
