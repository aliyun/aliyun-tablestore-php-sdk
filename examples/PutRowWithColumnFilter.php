<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
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
        'table_name' => 'MyTable', // 表名为 MyTable
        'primary_key_schema' => array (
            array('PK0', PrimaryKeyTypeConst::CONST_INTEGER), // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
            array('PK1', PrimaryKeyTypeConst::CONST_STRING)
        )
    ), // 第二个主键列名称为PK1, 类型为STRING
    
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU
            'write' => 0
        )
    ),

    'table_options' => array(
        'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
        'max_versions' => 2,    // 最大数据版本
        'deviation_cell_version_in_sec' => 86400  // 数据有效版本偏差，单位秒
    )
);
$otsClient->createTable ($request);
sleep (10);

$request0 = array (
    'table_name' => 'MyTable',
    'condition' => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
    'primary_key' => array ( // 主键
        array('PK0', 123),
        array('PK1', 'abc')
    ),
    'attribute_columns' => array( // 属性
        array('attr0', 456), // INTEGER类型
        array('attr1', 'Hangzhou'), // STRING类型
        array('attr2', 3.14), // DOUBLE类型
        array('attr3', true), // BOOLEAN类型
        array('attr4', 'a binary string', ColumnTypeConst::CONST_BINARY)  // BINARY类型
    )
);

$response = $otsClient->putRow ($request0);

$request1 = array (
    'table_name' => 'MyTable',
    'condition' => array (
        'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
        'column_condition' => array (                  //条件更新，满足则更新
            'column_name' => 'attr0',
            'value' => 456,
            'comparator' => ComparatorTypeConst::CONST_EQUAL
        )
    ),
    'primary_key' => array ( // 主键
        array('PK0', 123),
        array('PK1', 'abc')
    ),
    'attribute_columns' => array( // 属性
        array('attr0', 456), // INTEGER类型
        array('attr1', 'beijing'), // STRING类型
        array('attr2', 31.4), // DOUBLE类型
        array('attr3', false), // BOOLEAN类型
        array('attr4', 'a binary string', ColumnTypeConst::CONST_BINARY)  // BINARY类型
    )
);

$response = $otsClient->putRow ($request1);
print json_encode ($response);

/* 样例输出：

{
	"consumed": {
		"capacity_unit": {
			"read": 1,           // 本次操作消耗了1个读CU
			"write": 1           // 本次操作消耗了1个写CU
		}
	},
	"primary_key": [],        //主键列自增会有主键返回
	"attribute_columns": []
}
 */

