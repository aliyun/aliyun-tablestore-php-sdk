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
            )
        )
    )
); // 第三行

$response = $otsClient->batchGetRow ($request);
print json_encode ($response);

/* 样例输出：
{
    "tables": [
        {
            "table_name": "MyTable",
            "rows": [

                // 第一行的数据

                {
                    "is_ok": true,                  // 读取成功
                    "consumed": {
                        "capacity_unit": {
                            "read": 1,              // 这一行消耗了1个读CU
                            "write": 0
                        }
                    },
                    "row": {
                        "primary_key_columns": {
                            "PK0": 1,
                            "PK1": "Zhejiang"
                        },
                        "attribute_columns": {
                            "attr1": "Hangzhou"
                        }
                    }
                },

                // 第二行 ...
                // 第三行 ...
            ]
        }
    ]
}
*/

