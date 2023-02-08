#!/bin/bash

cts="lae lae_workers lae_worker lae_schedule lae_default_queue lae_cost_queue"

docker stop "$cts"
docker rm "$cts"
