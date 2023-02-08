#!/bin/bash

echo "Updating latte..."
docker pull ccr.ccs.tencentyun.com/laecloud/cafe:latte

echo "Updating vendor..."
docker run -it --rm --init --net=host -v /opt/lae:/opt/lae ccr.ccs.tencentyun.com/laecloud/cafe:latte composer install --no-dev

