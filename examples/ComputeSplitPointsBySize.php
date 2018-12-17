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
            array('primarykey_0', PrimaryKeyTypeConst::CONST_STRING), // 第一个主键列（又叫分片键）名称为primarykey_0, 类型为 STRING
            array('primarykey_1', PrimaryKeyTypeConst::CONST_STRING),
            array('primarykey_2', PrimaryKeyTypeConst::CONST_STRING),
            array('primarykey_3', PrimaryKeyTypeConst::CONST_STRING)
        )

    ),
    'reserved_throughput' => array (
        'capacity_unit' => array (
            'read' => 0, // 预留读写吞吐量设置为：1个读CU，和1个写CU
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

$response = $otsClient->computeSplitPointsBySize(array (
    'table_name' => 'MyTable',
    'split_size' => 1                   //单位是100MB
));
print json_encode ($response);

/* 样例输出：
{
	"consumed": {
		"capacity_unit": {
			"read": 1,
			"write": 0
		}
	},
	"primary_key_schema": [
		["primarykey_0", "STRING"],
		["primarykey_1", "STRING"],
		["primarykey_2", "STRING"],
		["primarykey_3", "STRING"]
	],
	"splits": [{                                           // 目前只有一个split, 分别[lower_bound, upper_bound, location]
		"lower_bound": [
			["primarykey_0", null, "INF_MIN"],             // lower_bound和upper_bound是可以传给getRange用的
			["primarykey_1", null, "INF_MIN"],
			["primarykey_2", null, "INF_MIN"],
			["primarykey_3", null, "INF_MIN"]
		],
		"upper_bound": [
			["primarykey_0", null, "INF_MAX"],
			["primarykey_1", null, "INF_MAX"],
			["primarykey_2", null, "INF_MAX"],
			["primarykey_3", null, "INF_MAX"]
		],
		"location": "AB82AB51600328350A977E7A0A2DFDE0"
	}]
}

// 持续输入数据之后，数据量增大后，会变成两个或者更多个splits.

{
	"consumed": {
		"capacity_unit": {
			"read": 2,
			"write": 0
		}
	},
	"primary_key_schema": [
		["primarykey_0", "STRING"],
		["primarykey_1", "STRING"],
		["primarykey_2", "STRING"],
		["primarykey_3", "STRING"]
	],
	"splits": [{                                            // 分成两个split, 分别[lower_bound, upper_bound, location]
		"lower_bound": [
			["primarykey_0", null, "INF_MIN"],              // lower_bound和upper_bound是可以传给getRange用的
			["primarykey_1", null, "INF_MIN"],
			["primarykey_2", null, "INF_MIN"],
			["primarykey_3", null, "INF_MIN"]
		],
		"upper_bound": [
			["primarykey_0", "primarykey_0_4_112\u0000"],
			["primarykey_1", null, "INF_MIN"],
			["primarykey_2", null, "INF_MIN"],
			["primarykey_3", null, "INF_MIN"]
		],
		"location": "AB82AB51600328350A977E7A0A2DFDE0"
	}, {
		"lower_bound": [
			["primarykey_0", "primarykey_0_4_112\u0000"],
			["primarykey_1", null, "INF_MIN"],
			["primarykey_2", null, "INF_MIN"],
			["primarykey_3", null, "INF_MIN"]
		],
		"upper_bound": [
			["primarykey_0", null, "INF_MAX"],
			["primarykey_1", null, "INF_MAX"],
			["primarykey_2", null, "INF_MAX"],
			["primarykey_3", null, "INF_MAX"]
		],
		"location": "AB82AB51600328350A977E7A0A2DFDE0"
	}]
}

*/

