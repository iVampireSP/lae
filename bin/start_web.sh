#!/bin/bash

docker run -itd --name=lae --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art works
