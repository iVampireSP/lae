#!/bin/bash

mkdir -p /opt/lae

{
    echo "alias latte='docker run -it --rm --init --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte'"
    echo "alias art='docker run -it --rm --init --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte php /opt/lae/artisan'"
    echo "alias composer='latte composer'"
    echo "alias php='latte php'"
    echo "alias lae='cd /opt/lae'"
} >>~/.bashrc

echo "Done! Please run 'source ~/.bashrc' to make it work."
