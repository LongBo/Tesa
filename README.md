# Tesa
PHP写的一个接口调试工具

# Usage
	php main.php -p openapi -h online -a user.info

## 参数:
 -p 
 项目，值：openapi | api ....

 -h
 环境，值: online | {testname} (例如：test03) | {devname}(例如：longbo) 

 -a
 值：接口名称, 例如：user.info 对应接口user/Info
 值：oauth, 表示模拟用户登录状态

## 其他说明：
 1)App/{appname}/actions/ 
 该目录下保存接口数据，参数字段value需自行填充，也可在Cli交互时填写

 2)App/{appname}/actions/_common/ 
 该目录下保存 公共参数、最近一次获取到的口令等

 3)App/{appname}/actions/_output/
 该目录下保存最近获取的各个接口的返回数据 (覆盖模式)