<?php
/**
 * 撮合进程
 */
date_default_timezone_set('PRC');
set_time_limit(0);

$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);

echo "开始订阅:\n";
$redis->subscribe(array('ask', 'bid'), 'callback');

// 回调函数
function callback($redis, $channelName, $message)
{
    $redis_callback = new Redis();
    $redis_callback->connect('127.0.0.1', 6379);
    $trade_id = substr($message, 0, strpos($message, ":"));
    $trade_amount = substr($message, strpos($message, ":") + 1);
    $trade_price = $redis_callback->zScore($channelName === "ask" ? "ask" : "bid", $message);
    echo "收到：这" . $channelName . "单的id: " . $trade_id . "，价格:" . $trade_price
        . "，数量 :" . $trade_amount . "，查找范围：" . (($channelName === "ask") ? $redis_callback->zScore("ask",
            $message)
            : "-inf～") . (($channelName === "bid") ? $redis_callback->zscore("bid", $message)
            : "+inf") . "\n";
    $opposition_trade_type = ($channelName === "ask") ? "bid" : "ask";

    // 如果有可以成交的记录
    while ($redis_callback->zCount($opposition_trade_type, ($channelName === "ask") ? $redis_callback->zScore("ask", $message) : "-inf", ($channelName === "bid") ? $redis_callback->zscore("bid", $message) : "+inf")) {
        $redis_callback->sort($opposition_trade_type, array('sort' => 'desc',
            'alpha' => TRUE,));
        // 通过价格去查找出对立单
        $opposition = ($channelName === "ask") ? $redis_callback->zRange
        ($opposition_trade_type, -1,
            -1)[0] : $redis_callback->zRevRange($opposition_trade_type, -1,
            -1)[0];

        // 对立价格，id，数量
        $opposition_price = $redis_callback->zScore($opposition_trade_type,
            $opposition);
        $opposition_id = substr($opposition, 0, strpos($opposition, ":"));
        $opposition_amount = substr($opposition, strpos($opposition, ":") + 1);

        echo "价格差最大的对手" . $opposition_trade_type . "单的id:" . $opposition_id .
            "，价格：" . $opposition_price . "，数量：" . $opposition_amount . "\n";
        $update_amount = $opposition_amount;
        if ($opposition_amount > $trade_amount) {
            $update_amount = $opposition_amount - $trade_amount;
            $redis_callback->zAdd($opposition_trade_type, $opposition_price, $opposition_id . ':' . $update_amount);
            // 更改数量大的
            echo "更新：这" . $opposition_trade_type . "单的id: " . $opposition_id .
                "，价格:" .
                $opposition_price . "，数量 :" . $update_amount . "，数量：" .
                $channelName . "<" . $opposition_trade_type . "\n";
        } elseif ($opposition_amount == $trade_amount) {
            echo "数量：bid=ask，更新后的数量为0\n";
        } else {
            $update_amount = $trade_amount - $opposition_amount;
            $redis_callback->zAdd($channelName, $trade_price, $trade_id . ':' . $update_amount);
            echo "更新：这" . $channelName . "单的id: " . $trade_id . "，价格:" .
                $trade_price . "，数量 :" . $update_amount . "，数量：" .
                $channelName .
                ">" . $opposition_trade_type . "\n";
        }
        // 删除这两单
        $redis_callback->zRem($channelName, $message);
        $redis_callback->zRem($opposition_trade_type, $opposition);
        // 成交记录：id, 价格，数量，时间
        $redis_callback->lPush("records" . ":" . $message, $trade_id, ($trade_price + $opposition_price) / 2, $update_amount);
        $redis_callback->publish("web:publish:records", date('Y-m-d H-i-s')
            . ":" . ($trade_price + $opposition_price) / 2 . ":" .
            $update_amount);
    }
    if (!is_null($redis_callback->getLastError())) {
        var_dump($redis_callback->getLastError());
    }
    $redis_callback->close();
}

return 0;