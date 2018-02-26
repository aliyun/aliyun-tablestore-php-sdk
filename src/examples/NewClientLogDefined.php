<?php

require(__DIR__ . "/../../vendor/autoload.php");
require(__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient(array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME,
));






function myDebugLogHandler($message) {
    // 实现你自己的日志处理函数
    print "hey, it's my debug log handler, and the message is $message\n";
}

function myErrorLogHandler($message) {
    // 实现你自己的日志处理函数
    print "hey, it's my error log handler, and the message is $message\n";
}

$otsClient->getClientConfig()->debugLogHandler = "myDebugLogHandler";
$otsClient->getClientConfig()->errorLogHandler = "myErrorLogHandler";

// 试试看效果怎么样
$otsClient->listTable(array());
$otsClient->describeTable(array('table_name' => "bad#table name"));
