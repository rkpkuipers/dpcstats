#!/bin/bash

/home/rkuipers/stats/statsrun/sob.php

for i in `ps --no-heading -o "pid cmd" -C "statsrun.php" | grep 60$ | awk '{print $1}'`
do
	kill -15 ${i}
done

/home/rkuipers/stats/statsrun/statsrun.php 60
