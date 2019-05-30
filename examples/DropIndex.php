<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

$request = array (
    'table_name' => 'MyTable', // 表名为 MyTable
    'index_name' => 'CreateIndex',
//    'index_name' => 'CreateTableWithIndex1',
//    'index_name' => 'CreateTableWithIndex2',

);
$otsClient->dropIndex( $request );

