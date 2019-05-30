<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient(array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME,
));

$response = $otsClient->describeSearchIndex(array(
    'table_name' => 'php_sdk_test',
    'index_name' => 'test_create_search_index'
));

print json_encode($response, JSON_PRETTY_PRINT);