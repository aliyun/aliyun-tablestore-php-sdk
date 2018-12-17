<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\PrimaryKeyOptionConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

$table = 'OTSPkAutoIncrSimpleExample';

// 封装一些函数示例：

function createTable($otsClient, $table)
{
    $tablebody = array(
        'table_meta' => array(
            'table_name' => $table,       // 设置表名
            'primary_key_schema' => array (
                array('gid', PrimaryKeyTypeConst::CONST_INTEGER),    // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
                array('uid', PrimaryKeyTypeConst::CONST_INTEGER, PrimaryKeyOptionConst::CONST_PK_AUTO_INCR)
                // 第二个主键列，名称为PK0, 类型为 INTEGER, 并且设置成列自增主键
            )
        ),
        'reserved_throughput' => array(
            'capacity_unit' => array(         // 预留读写吞吐量设置为：0个读CU，和0个写CU
                'read' => 0,
                'write' => 0
            )
        ),
        'table_options' => array(
            'time_to_live' => -1,             // 数据生命周期, -1表示永久，单位秒
            'max_versions' => 3,              // 最大数据版本，多版本数据支持，这里设置成3，一般1就可以
            'deviation_cell_version_in_sec' => 86400   // 数据有效版本偏差，单位秒
        )
    );
    $otsClient->createTable($tablebody);

    $tablename['table_name'] = $table;             // 查看表格描述信息，只需要设置table_name
    $table_meta = $otsClient->describeTable($tablename);

    print_r($table_meta);
    sleep (10);                          // 等待表格加载完毕
}

function deleteTable($otsClient, $table)
{
    try {
        $tablename['table_name'] = $table;             // 删除表格，只需要设置table_name
        $ret = $otsClient->deleteTable($tablename);
        print_r($ret);
    }catch (\Aliyun\OTS\OTSServerException $exception) {
        print_r($exception->getOTSErrorMessage());
    }
}

function putRow($otsClient, $table)
{
    $row = array(
        'table_name' => $table,
        'condition' => RowExistenceExpectationConst::CONST_IGNORE,
        'primary_key' => array(
            array('gid',  1),                      // 主键名，主键值， 注意是个list
            array('uid', null, PrimaryKeyTypeConst::CONST_PK_AUTO_INCR)    // 列自增设置, 主键值为null.
        ),

        'attribute_columns' => array(              // 属性列，注意是个list
            array('name', 'John'),                  // [属性名，属性值，属性类型，时间戳]， 没有设置可以忽略
            array('age', 20),
            array('address', 'Alibaba'),
            array('product', 'OTS'),
            array('married', false)
        ),
        'return_content' => array(
            'return_type' => ReturnTypeConst::CONST_PK     // 列自增需要主键返回需要设置return_type
        )
    );
    $ret = $otsClient->putRow($row);
    print_r($ret);

    $primaryKeys = $ret['primary_key'];
    return $primaryKeys;
}


function getRow($otsClient, $table, $primaryKeys)
{
    $rowToGet = array(
        'table_name' => $table,
        'primary_key' => $primaryKeys,
        'max_versions' => 1                   // 设置获取数据版本为最近一个
    );

    $ret = $otsClient->getRow($rowToGet);
    print_r($ret);
}

function updateRow($otsClient, $table, $primaryKeys)
{
    // 更详细的参考UpdateRow1.php ~ UpdateRow3.php
    $rowToChange = array(
        'table_name' => $table,                    //表名
        'primary_key' => $primaryKeys,
        'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
        'update_of_attribute_columns'=> array(      // 修改包括三种
            'PUT' => array (                        // 增加column
                array('language', 'Chinese'),
                array('address', 'aliyun')
            ),
            'DELETE' => array(),                    // 删除column的某一个版本
            'DELETE_ALL' => array(                  // 删除column的所有版本
                'married'
            )
        ),
        'return_content' => [                       // 同样设置自增列返回
            'return_type' => ReturnTypeConst::CONST_PK
        ]
    );
    $otsClient->updateRow ($rowToChange);

    $body = array (
        'table_name' => $table,
        'primary_key' => $primaryKeys,
        'columns_to_get' => array (),
        'max_versions' => 2                    //这里设置返回两个版本的数据，注意获取到的信息里面有两个版本的address.
    );
    $getrow = $otsClient->getRow ($body);

    print_r($getrow);

}

function deleteRow($otsClient, $table, $primaryKeys)
{
    $deleterow = array (
        'table_name' => $table,
        'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
        'primary_key' => $primaryKeys,
        'return_content' => array(                  // 同样设置自增列返回
            'return_type' => ReturnTypeConst::CONST_PK
        )
    );

    $ret = $otsClient->deleteRow($deleterow);
    print_r($ret);
}


deleteTable($otsClient, $table);
createTable($otsClient, $table);
// 写一行，并获取自增主键
$primaryKeys = putRow($otsClient, $table);    //NOTE：这里返回的主键是可以在其他地方直接用的
getRow($otsClient, $table, $primaryKeys);
updateRow($otsClient, $table, $primaryKeys);
deleteRow($otsClient, $table, $primaryKeys);
deleteTable($otsClient, $table);
