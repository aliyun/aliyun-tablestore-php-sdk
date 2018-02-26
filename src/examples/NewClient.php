<?php

require(__DIR__ . "/../../vendor/autoload.php");
















use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient(array(
    'EndPoint' => "http://<你的服务地址>",           # 这个地址从OTS的控制台的实例详情页面可以看到
    'AccessKeyID' => "你的Access Key ID",            # 请联系你的系统管理员获取
    'AccessKeySecret' => "你的Access Key Secret",    # 请联系你的系统管理员获取
    'InstanceName' => "你的实例名",
));

