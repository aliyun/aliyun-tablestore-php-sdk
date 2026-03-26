# Aliyun Tablestore SDK for PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/aliyun/aliyun-tablestore-sdk-php.svg)](https://packagist.org/packages/aliyun/aliyun-tablestore-sdk-php)
[![License](https://img.shields.io/packagist/l/aliyun/aliyun-tablestore-sdk-php.svg)](LICENSE.md)

Aliyun Tablestore SDK for PHP，用于通过 PHP 访问[阿里云表格存储（Tablestore）](https://www.aliyun.com/product/ots)服务。

## 版本兼容性

| SDK 版本 | PHP 版本要求 | 维护状态 |
|---------|------------|---------|
| 6.x（当前版本） | >= 8.2（支持 8.2、8.3、8.4、8.5） | **活跃维护** |
| 5.x | >= 5.5（支持 5.5 ~ 8.1） | 仅安全修复 |
| 4.x | >= 5.5（支持 5.5 ~ 7.2） | 停止维护 |
| 2.x | >= 5.3（支持 5.3 ~ 5.6） | 停止维护 |

> **升级提示**：如果你正在使用 PHP 8.2 或更高版本，建议升级到 SDK 6.x 以获得最佳兼容性和性能。如果你仍在使用 PHP 5.5 ~ 8.1，请继续使用 SDK 5.x 版本。

仅支持 **64 位** PHP 系统。

## 环境要求

- PHP >= 8.2（64 位）
- 扩展：`curl`、`openssl`、`json`
- [Composer](https://getcomposer.org/)

## 安装

### 通过 Composer 安装（推荐）

```bash
composer require aliyun/aliyun-tablestore-sdk-php
```

如果你需要安装旧版本以兼容低版本 PHP：

```bash
# 安装 5.x 版本（支持 PHP 5.5 ~ 8.1）
composer require aliyun/aliyun-tablestore-sdk-php:^5.0
```

### 手动安装

1. 下载 SDK 并解压到本地。
2. 安装依赖：

   ```bash
   composer install --no-dev
   ```

3. 在你的 PHP 代码中引入自动加载文件：

   ```php
   require_once 'vendor/autoload.php';
   ```

## 快速开始

```php
<?php
require_once 'vendor/autoload.php';

use Aliyun\OTS\OTSClient;

$client = new OTSClient([
    'EndPoint' => 'https://your-instance.cn-hangzhou.ots.aliyuncs.com',
    'AccessKeyID' => 'your-access-key-id',
    'AccessKeySecret' => 'your-access-key-secret',
    'InstanceName' => 'your-instance-name',
]);

// 列出所有表
$tables = $client->listTable([]);
print_r($tables);
```

## PHP 配置建议

在 `php.ini` 中进行以下配置（执行 `php --ini` 可查看配置文件位置）：

```ini
; 设置时区
date.timezone = Asia/Shanghai

; 建议将内存限制设置为 512M 或更高（GetRange 等接口可能占用较多内存）
memory_limit = 512M
```

## 运行示例程序

1. 修改 `examples/ExampleConfig.php`，填写你的 Tablestore 配置信息。
2. 运行示例：

   ```bash
   php examples/PKAutoIncrment.php
   ```

更多示例请参考 `examples/` 目录。

## 运行测试

1. 安装依赖：

   ```bash
   composer install
   ```

2. 设置环境变量：

   ```bash
   export SDK_TEST_ACCESS_KEY_ID=your-access-key-id
   export SDK_TEST_ACCESS_KEY_SECRET=your-access-key-secret
   export SDK_TEST_END_POINT=https://your-instance.cn-hangzhou.ots.aliyuncs.com
   export SDK_TEST_INSTANCE_NAME=your-instance-name
   ```

3. 执行测试：

   ```bash
   php vendor/bin/phpunit
   ```

## 文档

- [Tablestore 产品文档](https://help.aliyun.com/product/8315004_ots.html)
- [API 参考文档](docs/index.html)（HTML 格式，请在浏览器中打开）

## 变更日志

详见 [CHANGELOG.md](CHANGELOG.md)。

## 贡献

我们非常欢迎社区贡献代码。如果你发现了 Bug 或有功能建议，请提交 Issue 或 Pull Request。

## 许可证

本项目基于 [MIT 许可证](LICENSE.md) 开源。

## 联系我们

- [阿里云 Tablestore 官方网站](https://www.aliyun.com/product/ots)
- [阿里云 Tablestore 文档中心](https://help.aliyun.com/zh/tablestore)
- [阿里云工单系统](https://smartservice.console.aliyun.com/service/create-ticket)

### 扫码加入 Tablestore 讨论群
欢迎通过钉钉加入交流群：
* 为互联网应用、大数据、社交应用等开发者提供的最新技术交流群 36165029092（表格存储技术交流群-3）。
* 为物联网和时序模型开发者提供的技术交流群有44327024（物联网存储 IoTstore 开发者交流群）。
