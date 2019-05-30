<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

$request = array (
    'table_meta' => array (
        'table_name' => 'TransactionTable', // 表名为 TransactionTable
        'primary_key_schema' => array (
            array('PK0', PrimaryKeyTypeConst::CONST_INTEGER), // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
            array('PK1', PrimaryKeyTypeConst::CONST_STRING)   // 第二个主键列名称为PK1, 类型为STRING
        ),

    ),
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 0, // 预留读写吞吐量设置为：1个读CU，和1个写CU
            'write' => 0
        )
    ),
    'table_options' => array(
        'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
        'max_versions' => 1,    // 最大数据版本
        'deviation_cell_version_in_sec' => 86400  // 数据有效版本偏差，单位秒
    )
);
//$otsClient->createTable ($request);

$request = array (
    'table_name' => 'TransactionTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
    'primary_key' => array ( // 主键
        array('PK0', 0),
        array('PK1', '1')
    ),
    'attribute_columns' => array( // 属性
        array('attr0', 'origin value')
    )
);
$otsClient->putRow($request);

//StartLocalTransactionRequest
$response = $otsClient->startLocalTransaction (array (
    'table_name' => 'TransactionTable',
    'key' => array(
        array('PK0', 0)
    )
));

$putRequest = array (
    'table_name' => 'TransactionTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
    'primary_key' => array ( // 主键
        array('PK0', 0),
        array('PK1', '1')
    ),
    'attribute_columns' => array( // 属性
        array('attr0', 'new value')
    ),
    'transaction_id' => $response['transaction_id']
);
$otsClient->putRow($putRequest);

$otsClient->commitTransaction(array(
    'transaction_id' => $response['transaction_id']
));

$response = $otsClient->getRow(
    array(
        'table_name' => 'TransactionTable',
        'primary_key' => array ( // 主键
            array('PK0', 0),
            array('PK1', '1')
        ),
        'max_versions' => 1
    )
);

print json_encode ($response, JSON_PRETTY_PRINT);