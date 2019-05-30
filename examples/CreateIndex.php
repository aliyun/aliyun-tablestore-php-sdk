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
    'index_meta' => array (
        'name' => 'CreateIndex',
        'primary_key' => array('col1', 'PK1'),
        'defined_column' => array('col2')
    )
);
$otsClient->createIndex ( $request );

