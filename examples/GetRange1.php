<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\DirectionConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

// 关掉日志打印
$otsClient->getClientConfig ()->errorLogHandler = null;
$otsClient->getClientConfig ()->debugLogHandler = null;

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
            'read' => 0,
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

for($i = 0; $i < 6000; $i ++) {
  $request = array (
      'table_name' => 'MyTable',
      'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
      'primary_key' => array ( // 主键
          array('PK0', $i),
          array('PK1', 'abc')
      ),
      'attribute_columns' => array ( // 属性
          array('attr0', 456), // INTEGER类型
          array('attr1', 'Hangzhou'), // STRING类型
          array('attr2', 3.14), // DOUBLE类型
          array('attr3', true)
      ) // BOOLEAN类型

  );
  $otsClient->putRow ($request);
}

// 请注意，这个例子运行时PHP占用内存较大，在我们的测试环境中，需要将php.ini中的
// memory_limit 设置为 256M
$startPK = array (
    array('PK0', null, PrimaryKeyTypeConst::CONST_INF_MIN), // 'INF_MIN' 用来表示最小值
    array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MIN)
);

$endPK = array (
    array('PK0', null, PrimaryKeyTypeConst::CONST_INF_MAX), // 'INF_MAX' 用来表示最大值
    array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)

);

// 你同样可以用具体的值来表示 开始主键和结束主键，例如：
$startPK = array (
    array('PK0', 0),
    array('PK1', 'aaaa')
);
$endPK = array (
    array('PK0', 9999),
    array('PK1', 'zzzz')
);

while (! empty ($startPK)) {
  
  $request = array (
      'table_name' => 'MyTable',
      'max_versions' => 1,
      'direction' => DirectionConst::CONST_FORWARD, // 方向可以为 FORWARD 或者 BACKWARD
      'inclusive_start_primary_key' => $startPK, // 开始主键
      'exclusive_end_primary_key' => $endPK
  ) // 结束主键
;
  
  $response = $otsClient->getRange ($request);
  
  print "Read CU Consumed: {$response['consumed']['capacity_unit']['read']}\n";
  
  foreach ($response['rows'] as $rowData) {
    // 处理每一行数据
  }
  
  // 如果 next_start_primary_key 不为空，则代表
  // 范围内还有数据，需要继续读取
  $startPK = $response['next_start_primary_key'];
}

/* 单次GetRange的样例输出：
{
	"consumed": {
		"capacity_unit": {
			"read": 24,                     // 读CU消耗，实际值可能与此不同
			"write": 0
		}
	},
	"next_start_primary_key": null,
	"rows": [{
		"primary_key": [
			["PK0", 4997],
			["PK1", "abc"]
		],
		"attribute_columns": [
			["attr0", 456, "INTEGER", 1526421165346],
			["attr1", "Hangzhou", "STRING", 1526421165346],
			["attr2", 3.14, "DOUBLE", 1526421165346],
			["attr3", true, "BOOLEAN", 1526421165346]
		]
	},
    {
		"primary_key": [
			["PK0", 4998],
			["PK1", "abc"]
		],
		"attribute_columns": [
			["attr0", 456, "INTEGER", 1526421165355],
			["attr1", "Hangzhou", "STRING", 1526421165355],
			["attr2", 3.14, "DOUBLE", 1526421165355],
			["attr3", true, "BOOLEAN", 1526421165355]
		]
	}

    // 更多行。。。

    ]
}

*/

