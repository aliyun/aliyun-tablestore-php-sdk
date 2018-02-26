<?php
require (__DIR__ . "/../../vendor/autoload.php");
require (__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\ColumnTypeConst;
use Aliyun\OTS\RowExistenceExpectationConst;
use Aliyun\OTS\DirectionConst;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\LogicalOperatorConst;

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
            'read' => 0,
            'write' => 0
        )
    )
);
$otsClient->createTable ($request);
sleep (10);

for($i = 0; $i < 6000; $i ++) {
  $request = array (
      'table_name' => 'MyTable',
      'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
      'primary_key' => array ( // 主键
          'PK0' => $i,
          'PK1' => 'abc'
      ),
      'attribute_columns' => array ( // 属性
          'attr0' => 456, // INTEGER类型
          'attr1' => 'Hangzhou', // STRING类型
          'attr2' => 3.14, // DOUBLE类型
          'attr3' => true
      )
  ) // BOOLEAN类型
;
  $otsClient->putRow ($request);
}

// 请注意，这个例子运行时PHP占用内存较大，在我们的测试环境中，需要将php.ini中的
// memory_limit 设置为 256M

$startPK = array (
    'PK0' => 0,
    'PK1' => 'aaa'
);

$endPK = array (
    'PK0' => 9999,
    'PK1' => 'zzz'
);

$limit = 5500; // 假设我们需要获取 6000行数据的前 5500 条

while (! empty ($startPK) && $limit > 0) {
  
  $request = array (
      'table_name' => 'MyTable',
      'direction' => DirectionConst::CONST_FORWARD, // 方向可以为 FORWARD 或者 BACKWARD
      'inclusive_start_primary_key' => $startPK, // 开始主键
      'exclusive_end_primary_key' => $endPK, // 结束主键
      'limit' => $limit,
      'column_filter' => array (
          'logical_operator' => LogicalOperatorConst::CONST_AND,
          'sub_conditions' => array (
              array (
                  'column_name' => 'attr0',
                  'value' => 456,
                  'comparator' => ComparatorTypeConst::CONST_EQUAL
              ),
              array (
                  'column_name' => 'attr1',
                  'value' => 'Hangzhou',
                  'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
              )
          )
      )
  );
  
  $response = $otsClient->getRange ($request);
  
  print "Read CU Consumed: {$response['consumed']['capacity_unit']['read']}\n";
  
  foreach ($response['rows'] as $rowData ) {
        $limit --;
        
        // 处理每一行数据
    }
    
    $startPK = $response ['next_start_primary_key'];
    
    // 如果 next_start_primary_key 不为空并且 limit > 0 则循环继续
}

