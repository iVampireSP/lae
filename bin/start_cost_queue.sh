#!/bin/bash

docker run -itd --name=lae_cost_queue --init --restart=always --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte art queue:work --queue=host-cost
