## 项目说明

```angular2html
├── Dockerfile         
├── README.md
├── one.php           生成订单进程
├── supervisord.conf  supervisor配置文件(Dockerfile里配置用)
├── test.html         测试html
├── two.php           撮合进程
├── webdis.json       webdis配置文件(开启websocket)
```

## 安装配置

1.安装配置Redis

通过拉取镜像或者使用普通安装方法

```angular2html
docker pull redis
docker run --name db-redis -p 6379:6379 -d redis
```

2.安装php-redis

```angular2html
// Mac
brew install php55-redis
phpbrew --debug ext install github:phpredis/phpredis
// Linux
// Ubuntu
apt-get install php5-redis
// 源码编译
git clone git://github.com/nicolasff/phpredis.git
cd phpredis
phpize
./configure
make
sudo -s make install
sudo -s
echo "extension=redis.so">/etc/php5/conf.d/redis.ini
exit
```

3.本地测试

```angular2html
// 不带实时UI：
php two.php
php one.php

// 可以使用Redis客户端rdm进行查看

// 实时UI：
// 需要安装配置Webdis(安装过程请看## 安装配置Webdis ##)
// 配置Webdis
cp webdis.json ./webdis/
// 运行(可以用nohup后台运行)：
./webdis/webdis &

// 注意将test.html放置到本地服务器目录下
// 然后浏览器访问，注意控制台输出
```

## 安装配置Webdis

```angular2html
git clone git://github.com/nicolasff/webdis.git
cd webdis
make
// webdis(可以用nohup后台运行,遇到过段错误)
./webdis/webdis &
```
