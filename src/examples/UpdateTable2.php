<?php
require (__DIR__ . "/../../vendor/autoload.php");
require (__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\ColumnTypeConst;

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
            'PK0' => ColumnTypeConst::CONST_INTEGER, // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
            'PK1' => ColumnTypeConst::CONST_STRING
        ) // 第二个主键列名称为PK1, 类型为STRING

    ),
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 1, // 预留读写吞吐量设置为：1个读CU，和1个写CU
            'write' => 1
        )
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
