<?php
require (__DIR__ . '/../../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\ColumnTypeConst;
use Aliyun\OTS\RowExistenceExpectationConst;

date_default_timezone_set ('Asia/Shanghai');

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
        )
    ) // 第二个主键列名称为PK1, 类型为STRING

    ,
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU
            'write' => 0
        )
    )
);
$otsClient->createTable ($request);
sleep (10);

$request = array (
    'table_name' => 'MyTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
    'primary_key' => array ( // 主键
        'PK0' => 123,
        'PK1' => 'abc'
    ),
    'attribute_columns' => array ( // 属性
        'attr0' => 456, // INTEGER类型
        'attr1' => 'Hangzhou', // STRING类型
        'attr2' => 3.14, // DOUBLE类型
        'attr3' => true, // BOOLEAN类型
        'attr4' => false, // BOOLEAN类型
        'attr5' => array ( // BINARY类型
            'type' => 'BINARY',
            'value' => 'a binary string'
        )
    )
);

$response = $otsClient->putRow ($request);

$request = array (
    'table_name' => 'MyTable',
    'condition' => array (
        'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
        'column_filter' => array ( // 对要操作的目标行的数据进行判断，如果其attr0列为456的时候才删除该目标列
            'column_name' => 'attr0',
            'value' => 456,
            'comparator' => ComparatorTypeConst::CONST_EQUAL
        )
    ),
    'primary_key' => array ( // 主键
        'PK0' => 123,
        'PK1' => 'abc'
    ),
    'attribute_columns_to_delete' => array (
        'attr1', // 指定删除 attr1 attr2 两列
        'attr2'
    )
);
$response = $otsClient->updateRow ($request);
print json_encode ($response);

// $request = array(
// 		'table_name' => 'MyTable',
// 		'primary_key' => array(          // 主键
// 				'PK0' => 123,
// 				'PK1' => 'abc',
// 		),
// 		'columns_to_get' => array(
// 				'attr0',
// 				'attr1',
// 				'attr2',
// 				'attr3',
// 				'attr4',
// 				'attr5'
// 		)
// );
// $response = $otsClient->getRow($request);
// print 'verify result: \n'.json_encode($response);

/* 样例输出：
 {
 	'consumed': {
 		'capacity_unit': {
 			'read': 0,
 			'write': 1           // 本次操作消耗了1个写CU
 		}
 	}
 }
 */

