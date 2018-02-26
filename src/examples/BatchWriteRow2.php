<?php
require (__DIR__ . "/../../vendor/autoload.php");
require (__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;
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
            'update_rows' => array (
                array ( // 第一行
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        'PK0' => 1,
                        'PK1' => 'Zhejiang'
                    ),
                    
                    // 用 attribute_columns_to_put 指定要更新或者追加的列
                    'attribute_columns_to_put' => array (
                        'attr1' => 'Chandler Bing',
                        'attr2' => 256
                    )
                ),
                array ( // 第二行
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        'PK0' => 2,
                        'PK1' => 'Jiangsu'
                    ),
                    
                    // 用 attribute_columns_to_delete 指定要删除的列
                    'attribute_columns_to_delete' => array (
                        'attr1',
                        'attr2'
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
    "tables": [
        {
            "table_name": "MyTable",
            "put_rows": [],
            "update_rows": [
                {
                    "is_ok": true,
                    "consumed": {
                        "capacity_unit": {
                            "read": 0,
                            "write": 1
                        }
                    }
                },
                {
                    "is_ok": true,
                    "consumed": {
                        "capacity_unit": {
                            "read": 0,
                            "write": 1
                        }
                    }
                }
            ],
            "delete_rows": []
        }
    ]
}
*/

