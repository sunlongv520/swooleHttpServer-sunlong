<?php

define("ROOT_PAHT",dirname(dirname(__DIR__)));

$GLOBAL_CONFIGS=[
   "db"=>require_once(__DIR__."/db.php"),
    "dbpool"=>require_once(__DIR__."/dbpool.php"),
    "redis"=>require_once(__DIR__."/redis.php"),
    "redispool"=>require_once(__DIR__."/redispool.php"),
];