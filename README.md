Aliyun TableStore SDK for PHP
==================================

# 说明

Aliyun OTS SDK for PHP，用来通过PHP访问阿里云OTS服务。

适用于PHP 5.3 以及以上版本。

当前仅支持Linux，其他系统需要用户自己改造代码支持

# 使用步骤

1. 请确认你的PHP版本为 5.3 或更高。你可以通过运行 php --version 获知你当前使用的PHP版本。

2. 设置PHP的时区，在 php.ini（要知道你正在使用的php.ini文件的位置，请执行命令 php --ini）中添加一行：
   
   date.timezone = Asia/Shanghai  （请根据你当地的时区进行设置）

3. 设置PHP的内存使用限制为512M或者更高。同样是在 php.ini 中修改：
  
   memory_limit = 512M

4. 下载SDK并解压到本地。

5. 安装依赖。在解压后的目录中执行命令： 

   php tools/composer.phar install

6. 生成 autoload。 在解压后的目录中执行命令：

   php tools/composer.phar dumpautoload --no-dev

   这条命令会生成 vendor/autoload.php 文件。

7. 在你的PHP代码文件中引用（require）上一个步骤中生成的 vendor/autoload.php 文件。

# 编程文档

我们提供了HTML格式的文档，请在浏览器中打开这些文档。

1. 文档主页：docs/index.html

2. SDK的调用入口 OTSClient：docs/classes/Aliyun.OTS.OTSClient.html

   这个文档中有丰富的代码样例，详细说明了每个API的使用方法。

3. 客户端配置：docs/classes/Aliyun.OTS.OTSClientConfig.html

4. 重试策略：docs/namespaces/Aliyun.OTS.Retry.html

5. 服务端返回的错误：docs/classes/Aliyun.OTS.OTSServerException.html

6. 客户端返回的错误：docs/classes/Aliyun.OTS.OTSClientException.html

# 帮助和支持 FAQ

* 阿里云官网论坛: http://bbs.aliyun.com/thread/226.html
* 云栖社区: https://yq.aliyun.com/groups/82
* TableStore官网:  http://ots.aliyun.com
