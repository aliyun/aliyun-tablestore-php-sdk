<?php
require (__DIR__ . "/../../vendor/autoload.php");
require (__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\ComparatorTypeConst;
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
    'table_name' => 'MyTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
    'primary_key' => array (
        'PK0' => 1,
        'PK1' => 'Zhejiang'
    ),
    'attribute_columns' => array (
        'attr1' => 'Hangzhou'
    )
);
$response = $otsClient->putRow ($request);

$request = array (
    'table_name' => 'MyTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
    'primary_key' => array (
        'PK0' => 2,
        'PK1' => 'Jiangsu'
    ),
    'attribute_columns' => array (
        'attr1' => 'Nanjing'
    )
);
$response = $otsClient->putRow ($request);

$request = array (
    'table_name' => 'MyTable',
    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
    'primary_key' => array (
        'PK0' => 3,
        'PK1' => 'Guangdong'
    ),
    'attribute_columns' => array (
        'attr1' => 'Shenzhen'
    )
);
$response = $otsClient->putRow ($request);

$request = array (
    'tables' => array (
        array (
            'table_name' => 'MyTable',
            'rows' => array (
                array (
                    'primary_key' => array (
                        'PK0' => 1,
                        'PK1' => 'Zhejiang'
                    )
                ), // 第一行
                array (
                    'primary_key' => array (
                        'PK0' => 2,
                        'PK1' => 'Jiangsu'
                    )
                ), // 第二行
                array (
                    'primary_key' => array (
                        'PK0' => 3,
                        'PK1' => 'Guangdong'
                    )
                )
            ), // 第三行
            'column_filter' => array (
                'column_name' => 'attr1',
                'value' => 'Shenzhen',
                'comparator' => ComparatorTypeConst::CONST_NOT_EQUAL
            )
        )
    )
);

$response = $otsClient->batchGetRow ($request);

// 处理返回的每个表
foreach ($response['tables'] as $tableData) {
  print "Handling table {$tableData['table_name']} ...\n";
  
  // 处理这个表下的每行数据
  foreach ($tableData['rows'] as $rowData) {
    
    if ($rowData['is_ok']) {
      
      // 处理读取到的数据
    } else {
            
            // 处理出错
            print "Error: {$rowData['error']['code']} {$rowData['error']['message']}\n";
        }
    }
}

