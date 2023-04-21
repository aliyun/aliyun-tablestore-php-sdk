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
        'name' => 'CreateDefaultGlobalIndex',
        'primary_key' => array('col1', 'PK1'),
        'defined_column' => array('col2'),
        'index_type' => IndexTypeConst::GLOBAL_INDEX,
        'index_update_mode' => IndexUpdateModeConst::ASYNC_INDEX
    ),
    'include_base_data' => true // false-仅增量（缺省默认）；true-包含存量；
);
$otsClient->createIndex ( $request );

$createGlobalequest = array (
    'table_name' => 'MyTable', // 表名为 MyTable
    'index_meta' => array (
        'name' => 'CreateGlobalIndex',
        'primary_key' => array('PK0', 'col1'),
        'defined_column' => array('col2'),
        'index_type' => IndexTypeConst::LOCAL_INDEX,
        'index_update_mode' => IndexUpdateModeConst::SYNC_INDEX
    )
);
$otsClient->createIndex ( $createGlobalequest );

$createLocalRequest = array (
    'table_name' => 'MyTable', // 表名为 MyTable
    'index_meta' => array (
        'name' => 'CreateLocalIndex',
        'primary_key' => array('col1', 'PK1'),
        'defined_column' => array('col2')
    )
);
$otsClient->createIndex ( $createLocalRequest );

