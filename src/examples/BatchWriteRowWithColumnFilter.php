<?php
require (__DIR__ . "/../../vendor/autoload.php");
require (__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\LogicalOperatorConst;
use Aliyun\OTS\ColumnTypeConst;
use Aliyun\OTS\RowExistenceExpectationConst;

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
    ), // 第二个主键列名称为PK1, 类型为STRING
    
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
    'tables' => array (
        array (
            'table_name' => 'MyTable',
            'put_rows' => array (
                array (
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        'PK0' => 1,
                        'PK1' => 'Zhejiang'
                    ),
                    'attribute_columns' => array (
                        'attr1' => 'Chandler Bing',
                        'attr2' => 256
                    )
                ),
                array (
                    'condition' => array (
                        'row_existence' => RowExistenceExpectationConst::CONST_IGNORE
                    ),
                    'primary_key' => array (
                        'PK0' => 2,
                        'PK1' => 'Zhejiang'
                    ),
                    'attribute_columns' => array (
                        'attr1' => 'Chandler Bing',
                        'attr2' => 256
                    )
                )
            ),
            'update_rows' => array (
                array ( // 第一行
                    'condition' => array (
                        'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                        'column_filter' => array (
                            'column_name' => 'attr2',
                            'value' => 256,
                            'comparator' => ComparatorTypeConst::CONST_NOT_EQUAL
                        )
                    ),
                    'primary_key' => array (
                        'PK0' => 3,
                        'PK1' => 'Zhejiang'
                    ),
                    'attribute_columns_to_put' => array (
                        'attr1' => 'Chandler Bing',
                        'attr2' => 256
                    )
                ),
                array ( // 第二行
                    'condition' => array (
                        'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                        'column_filter' => array (
                            'logical_operator' => LogicalOperatorConst::CONST_OR,
                            'sub_conditions' => array (
                                array (
                                    'column_name' => 'attr2',
                                    'value' => 256,
                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                ),
                                array (
                                    'column_name' => 'attr1',
                                    'value' => 333,
                                    'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                )
                            )
                        )
                    ),
                    'primary_key' => array (
                        'PK0' => 4,
                        'PK1' => 'Jiangsu'
                    ),
                    'attribute_columns_to_delete' => array (
                        'attr1',
                        'attr2'
                    )
                )
            ),
            'delete_rows' => array (
                array ( // 第一行
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        'PK0' => 5,
                        'PK1' => 'Zhejiang'
                    )
                ),
                array ( // 第二行
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        'PK0' => 6,
                        'PK1' => 'Jiangsu'
                    )
                )
            )
        )
    )
);

$response = $otsClient->batchWriteRow ($request);
print json_encode ($response);

// 处理返回的每个表
foreach ($response['tables'] as $tableData) {
  print "Handling table {$tableData['table_name']} ...\n";
  
  // 处理这个表下的PutRow返回的结果
  $putRows = $tableData['put_rows'];
  
  foreach ($putRows as $rowData) {
    
    if ($rowData['is_ok']) {
      // 写入成功
      print "Capacity Unit Consumed: {$rowData['consumed']['capacity_unit']['write']}\n";
    } else {
      
      // 处理出错
      print "Error: {$rowData['error']['code']} {$rowData['error']['message']}\n";
    }
  }
  
  // 处理这个表下的UpdateRow返回的结果
  $updateRows = $tableData['update_rows'];
  
  foreach ($updateRows as $rowData) {
    // 像 PutRow一样处理 。。。
    if ($rowData['is_ok']) {
      // 写入成功
      print "Capacity Unit Consumed: {$rowData['consumed']['capacity_unit']['write']}\n";
    } else {
      
      // 处理出错
      print "Error: {$rowData['error']['code']} {$rowData['error']['message']}\n";
    }
  }
  
  // 处理这个表下的DeleteRow返回的结果
  $deleteRows = $tableData['delete_rows'];
  
  foreach ($deleteRows as $rowData) {
    
    if ($rowData['is_ok']) {
      // 写入成功
      print "Capacity Unit Consumed: {$rowData['consumed']['capacity_unit']['write']}\n";
        } else {
            // 处理出错
            print "Error: {$rowData['error']['code']} {$rowData['error']['message']}\n";
        }
    }
}

