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

$response = $otsClient->describeTable (array (
    'table_name' => "MyTable"
));
print json_encode ($response);

/* 样例输出：

{
    "table_meta": {
        "table_name": "MyTable",
        "primary_key_schema": {
            "PK0": "INTEGER",
            "PK1": "STRING"
        }
    },
    "capacity_unit_details": {
        "capacity_unit": {
            "read": 1,
            "write": 1
        },
        "last_increase_time": 1442224106,
        "last_decrease_time": null,
        "number_of_decreases_today": 0
    }
}

*/

