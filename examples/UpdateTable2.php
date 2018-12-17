<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

$request = array (
    'table_meta' => array (
        'table_name' => 'MyTable', // 表名为 MyTable
        'primary_key_schema' => array (
            array('PK0', PrimaryKeyTypeConst::CONST_INTEGER), // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
            array('PK1', PrimaryKeyTypeConst::CONST_STRING)
        ) // 第二个主键列名称为PK1, 类型为STRING

    ),
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 1, // 预留读写吞吐量设置为：1个读CU，和1个写CU
            'write' => 1
        )
    ),
    'table_options' => array(
        'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
        'max_versions' => 2,    // 最大数据版本
        'deviation_cell_version_in_sec' => 86400  // 数据有效版本偏差，单位秒
    )
);
$otsClient->createTable ($request);
sleep (125);

$request = array (
    'table_name' => 'MyTable',
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 2
        ) // 只更新读预留吞吐量

    )
);

// 或者

$request = array (
    'table_name' => 'MyTable',
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'write' => 2
        ) // 只更新写预留吞吐量

    )
);

$response = $otsClient->updateTable($request);
