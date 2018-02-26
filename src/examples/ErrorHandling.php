<?php
require (__DIR__ . "/../../vendor/autoload.php");
require (__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

use Aliyun\OTS\OTSServerException as OTSServerException;

try {
    // 在这个例子中，我们构造了一个非法的表名
    $request = array("table_name" => "bad#table name");
    $response = $otsClient->describeTable($request);
} catch (OTSServerException $e) {
    // 按照你的需要处理这个异常
    print $e->getOTSErrorCode() . "\n";
    print $e->getOTSErrorMessage() . "\n";
}
