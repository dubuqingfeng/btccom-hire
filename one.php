<?php
/**
 *
 *
 */
date_default_timezone_set('PRC');
set_time_limit(0);

// pconnect连接
$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);
// 这里实际获取最大的no值
$records_no = 1;
$methods = array('ask', 'bid');
$redis->flushAll();
while (true) {
    $rand_keys = array_rand($methods, 1);
    $amount = mt_rand(5, 15);
    $price = mt_rand(300, 500);
    // 发布
    $redis->publish($methods[$rand_keys], $records_no . ':' . $amount);
    $redis->zadd($methods[$rand_keys], $price, $records_no . ':' . $amount);
    // 发布到websocket
    $redis->publish("web:publish:" . $methods[$rand_keys], $records_no . ':'
        . $amount . ':' . $price);
    if (!is_null($redis->getLastError())) {
        var_dump($redis->getLastError());
    }
    echo $records_no . " " . date('Y-m-d H:i:s') . " " . $methods[$rand_keys] . " OK!\n";
    $records_no++;
    sleep(1);
}
return 0;