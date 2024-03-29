<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\OperationTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\OTSClient as OTSClient;

// Put 后做Increment的 Update示例，支持返回修改后的结果

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

$request = array (
    'table_name' => 'MyTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
    'primary_key' => array ( // 主键
        array('PK0', 123),
        array('PK1', 'inc')
    ),
    'attribute_columns' => array( // 属性
        array('attr0', 1), // INTEGER类型
        array('attr1', 0), // INTEGER类型
    )
);

$response = $otsClient->putRow ($request);

$request = array (
    'tables' => array (
        array (
            'table_name' => 'MyTable',
            'rows' => array (               //操作列表
                array ( // 第一行
                    'operation_type' => OperationTypeConst::CONST_UPDATE,            //操作是PUT
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK0', 123),
                        array('PK1', 'inc')
                    ),
                    'update_of_attribute_columns' => array (
                        'INCREMENT' => array (     // 三种操作类型， PUT，DELETE，DELETE_ALL
                            array('attr0', 1), // 自增列
                        ),
                        'PUT' => array(
                            array('attr1', 1), // INTEGER类型
                        )
                    ),
                    'return_content' => array(
                        'return_type' => ReturnTypeConst::CONST_AFTER_MODIFY,
                        'return_column_names' => array('attr0')
                    )
                )
            )
        )
    )
);
$response = $otsClient->batchWriteRow ($request);
print json_encode ($response);

/* 样例输出：
{
	"tables": [{
		"rows": [{
			"is_ok": true,
			"consumed": {
				"capacity_unit": {
					"read": 1,
					"write": 1
				}
			},
			"primary_key": [],
			"attribute_columns": [
				["attr0", 2, "INTEGER", 1673605952594]
			]
		}],
		"table_name": "MyTable"
	}]
}
*/

