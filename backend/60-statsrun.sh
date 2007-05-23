#!/bin/bash

/home/rkuipers/stats/statsrun/sob.php

pkill -f "statsrun.php 60"
/home/rkuipers/stats/statsrun/statsrun.php 60
