<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\OperationTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\OTSClient as OTSClient;

// 多个UPDATE in BatchWriteRows 示例


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
    'tables' => array (
        array (
            'table_name' => 'MyTable',
            'rows' => array (
                array ( // 第一行
                    'operation_type' => OperationTypeConst::CONST_UPDATE,            //操作是UDPATE
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK0', 1),
                        array('PK1', 'Zhejiang')
                    ),
                    
                    // 用 attribute_columns/put 指定要更新或者追加的列, 同UpdateTable格式
                    'update_of_attribute_columns'=> array(
                        'PUT' => array (
                            array('attr1', 'Chandler Bing'),
                            array('attr2',  256)
                        ),
                        'DELETE_ALL' => array(
                            'attr3'
                        )
                    )
                ),
                array ( // 第二行
                    'operation_type' => OperationTypeConst::CONST_UPDATE,            //操作是UDPATE
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK0', 2),
                        array('PK1', 'Jiangsu')
                    ),

                    'update_of_attribute_columns'=> array(
                        'PUT' => array (),
                        // 用 attribute_columns/delete 指定要删除的列
                        'DELETE_ALL' => array(
                            'attr1',
                            'attr2'
                        )
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
		"table_name": "MyTable",
		"rows": [{
			"is_ok": true,
			"consumed": {
				"capacity_unit": {
					"read": 0,
					"write": 1
				}
			},
			"primary_key": [],
			"attribute_columns": []
		}, {
			"is_ok": true,
			"consumed": {
				"capacity_unit": {
					"read": 0,
					"write": 1
				}
			},
			"primary_key": [],
			"attribute_columns": []
		}]
	}]
}*/

