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
            'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU，所有操作全部按量计费
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
sleep (125);

// 请注意调用UpdateTable有2分钟一次的限制，具体情况请参考OTS官网文档

$request = array (
    'table_name' => 'MyTable',
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 1, // 预留读写吞吐量设置为：1个读CU，和1个写CU
            'write' => 1
        )
    ),
    'table_options' => array(
        'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
        'max_versions' => 1,    // 最大数据版本
        'deviation_cell_version_in_sec' => 33333  // 数据有效版本偏差，单位秒
    )
)
;
$response = $otsClient->updateTable ($request);
print json_encode ($response);

/* 样例输出：

{
	"capacity_unit_details": {
		"capacity_unit": {
			"read": 1,
			"write": 1
		},
		"last_increase_time": 1526367938,
		"last_decrease_time": 0
	},
	"table_options": {
		"time_to_live": -1,
		"max_versions": 1,
		"deviation_cell_version_in_sec": 33333
	}
}

*/

