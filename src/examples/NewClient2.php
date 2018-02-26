<?php

require(__DIR__ . "/../../vendor/autoload.php");
















use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Retry\DefaultRetryPolicy as DefaultRetryPolicy;
use Aliyun\OTS\Retry\NoRetryPolicy as NoRetryPolicy;

function myErrorLogHandler($message) {
    print $message . "\n";
}

function myDebugLogHandler($message) {
    print $message . "\n";
}


$otsClient = new OTSClient(array(
    'EndPoint' => "http://<你的服务地址>",           # 这个地址从OTS的控制台的实例详情页面可以看到
    'AccessKeyID' => "你的Access Key ID",            # 请联系你的系统管理员获取
    'AccessKeySecret' => "你的Access Key Secret",    # 请联系你的系统管理员获取
    'InstanceName' => "你的实例名",

    // 以下是可选参数
    'ConnectionTimeout' => 2.0,                      # 与OTS建立连接的最大延时，默认 2.0秒
    'SocketTimeout' => 2.0,                          # 每次请求响应最大延时，默认2.0秒

    // 重试策略，默认为 DefaultRetryPolicy
    // 如果要关闭重试，可以设置为： 'RetryPolicy' => new NoRetryPolicy(),
    // 如果要自定义重试策略，你可以继承 \Aliyun\OTS\Retry\RetryPolicy 接口构造自己的重试策略
    'RetryPolicy' => new DefaultRetryPolicy(),

    // Error级别日志处理函数，用来打印OTS服务端返回错误时的日志
    // 如果设置为null则为关闭log
    'ErrorLogHandler' => "myErrorLogHandler",

    // Debug级别日志处理函数，用来打印正常的请求和响应信息
    // 如果设置为null则为关闭log
    'DebugLogHandler' => 'myDebugLogHandler',
));

