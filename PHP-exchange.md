# 概述

设计一个为证券交易市场显示下单列表和成交列表的 Demo。

一个单(Order)由（单号(number)，买(bid)/卖(ask)，数量(quantity)，价格(price)）表示，例如

- (1001, ask, 6, 480)
- (1002, bid, 8, 470)
- (1003, bid, 5, 460)

当市场上出现一个买单的价格高于一个卖单的时候，两单（互为对手单）可以成交，成交价格为两单价格的平均数。当一个单可以和多个对手单成交时，优先和价格更优的先成交；价格相同的优先和编号更小的成交。

比如上面的例子，最低的卖单价(480)比最高的买单价(470)还要高，所以无成交。若此时新报一单（1004, ask, 10, 460)，则该单先与1002成交，成交价为465，成交量为min(10, 8) = 8；而后再与1003成交，成交价为460，成交量为min(10 - 8, 5) = 2。之后Order book变为

- (1001, ask, 6, 480)
- (1003, bid, 3, 460)

## 功能需求

1. 后端有 PHP 进程每秒生成一个新的order, order number从1开始连续递增；价格均为整数，成为下单队列；类型可能为买单，也可能为卖单
1. 后端另一 PHP 进程负责照概述中介绍的方案撮合交易，成为成交队列。

请设计存储方案、买卖单撮合方案。

## 加分项

实现一个简单的UI,用于查看买单、卖单和成交队列，类似于 https://www.okcoin.com/market-btc.html 下方的买卖单和成交单显示。

## 代码提交

请按要求开发并生成UI。开发完成后请将代码上传至你的github repo以供查看。

