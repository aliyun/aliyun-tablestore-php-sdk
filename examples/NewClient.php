<?php

require (__DIR__ . '/../vendor/autoload.php');
















use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient(array(
    'EndPoint' => "http://<Your endpoint>",           # 这个地址从OTS的控制台的实例详情页面可以看到
    'AccessKeyID' => "Your Access Key ID",            # 请联系你的系统管理员获取
    'AccessKeySecret' => "Your Access Key Secret",    # 请联系你的系统管理员获取
    'InstanceName' => "Your instance name",
));

