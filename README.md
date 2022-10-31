Aliyun TableStore SDK for PHP - Version 5
==================================

# 说明

Aliyun OTS SDK for PHP，用来通过PHP访问阿里云OTS服务。

适用于PHP 5.5 及以上版本，包括7.0、7.1、7.2、8.0、8.1。只支持64位的PHP系统。推荐使用PHP7，以得到最好的性能。

当前仅支持Linux，其他系统需要用户自己改造代码支持。

# 使用步骤

1. 请确认你的PHP版本为 5.5 或更高。你可以通过运行 php --version 获知你当前使用的PHP版本。

2. 设置PHP的时区，在 php.ini（要知道你正在使用的php.ini文件的位置，请执行命令 php --ini）中添加一行：
   
   date.timezone = Asia/Shanghai  （请根据你当地的时区进行设置）

3. 设置PHP的内存使用限制为512M或者更高。同样是在 php.ini 中修改：
  
   memory_limit = 512M

4. 下载SDK并解压到本地。

5. 安装依赖。在解压后的目录中执行命令： 

   php tools/composer.phar install --no-dev

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

### 运行Sample程序

1. 修改 `examples/ExampleConfig.php`， 补充配置信息
2. 执行 `cd examples/`
3. 选择需要的样例运行，例如 `php PKAutoIncrment.php`, 表格列自增功能的示例。

### 运行单元测试

1. 执行`composer install`下载依赖的库
2. 如果是php 7.2，还需要 `composer require --dev phpunit/phpunit "^5.7.11"`，升级下phpunit的版本，才能支持。
3. 设置环境变量

        export SDK_TEST_ACCESS_KEY_ID=access-key-id
        export SDK_TEST_ACCESS_KEY_SECRET=access-key-secret
        export SDK_TEST_END_POINT=endpoint
        export SDK_TEST_INSTANCE_NAME=instance-name

4. 执行 `php vendor/bin/phpunit`

## 贡献代码
 - 我们非常欢迎大家为TableStore PHP SDK以及其他阿里云SDK贡献代码

# 帮助和支持 FAQ

- [阿里云TableStore官方网站](http://www.aliyun.com/product/ots)
- [阿里云TableStore官方论坛](http://bbs.aliyun.com)
- [阿里云TableStore官方文档中心](https://help.aliyun.com/product/8315004_ots.html)
- [阿里云云栖社区](http://yq.aliyun.com)
- [阿里云工单系统](https://workorder.console.aliyun.com/#/ticket/createIndex)

### 扫码加入TableStore讨论群，和我们直接交流讨论
![tablestoregroup](https://tablestore-doc.oss-cn-hangzhou.aliyuncs.com/tablestore_dingding.jpg?x-oss-process=image/resize,m_lfit,h_400)
