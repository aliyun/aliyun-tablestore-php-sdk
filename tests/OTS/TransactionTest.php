<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\LogicalOperatorConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class TransactionTest extends SDKTestBase {

    private static $transactionTableName = 'TransactionTable';

    public static function setUpBeforeClass()
    {

    }

    public static function tearDownAfterClass()
    {

    }

    /*
     * Transaction 事务需要建表后开通，才会支持，无法自动测试，此处使用已有表（已开通事务）
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet参数为4个属性列，期望读出所有4个属性列。
     */
    public function testAbort() {
//        'table_name' => 'TransactionTable', // 表名为 TransactionTable
//        'primary_key_schema' => array (
//            array('PK0', PrimaryKeyTypeConst::CONST_INTEGER), // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
//            array('PK1', PrimaryKeyTypeConst::CONST_STRING)   // 第二个主键列名称为PK1, 类型为STRING
//        )

        $request = array (
            'table_name' => 'TransactionTable',
            'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
            'primary_key' => array ( // 主键
                array('PK0', 0),
                array('PK1', '1')
            ),
            'attribute_columns' => array( // 属性
                array('attr0', 'origin value')
            )
        );
        $this->otsClient->putRow($request);

        //StartLocalTransactionRequest
        $response = $this->otsClient->startLocalTransaction (array (
            'table_name' => 'TransactionTable',
            'key' => array(
                array('PK0', 0)
            )
        ));

        $updateRequest = array(
            'table_name' => 'TransactionTable',
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK0', 0),
                array('PK1', '1')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('attr0', 'new value')
                )
            ),
            'transaction_id' => $response['transaction_id']
        );
        $this->otsClient->updateRow($updateRequest);

        $this->otsClient->abortTransaction(array(
            'transaction_id' => $response['transaction_id']
        ));

        $response = $this->otsClient->getRow(
            array(
                'table_name' => 'TransactionTable',
                'primary_key' => array ( // 主键
                    array('PK0', 0),
                    array('PK1', '1')
                ),
                'max_versions' => 1
            )
        );

//        print json_encode ($response, JSON_PRETTY_PRINT);
        $this->assertEquals($response['attribute_columns'][0][0], 'attr0');
        $this->assertEquals($response['attribute_columns'][0][1], 'origin value');
        $this->assertEquals($response['attribute_columns'][0][2], 'STRING');
    }

    public function testCommit() {
//        'table_name' => 'TransactionTable', // 表名为 TransactionTable
//        'primary_key_schema' => array (
//            array('PK0', PrimaryKeyTypeConst::CONST_INTEGER), // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
//            array('PK1', PrimaryKeyTypeConst::CONST_STRING)   // 第二个主键列名称为PK1, 类型为STRING
//        )

        $request = array (
            'table_name' => 'TransactionTable',
            'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
            'primary_key' => array ( // 主键
                array('PK0', 0),
                array('PK1', '1')
            ),
            'attribute_columns' => array( // 属性
                array('attr0', 'origin value')
            )
        );
        $this->otsClient->putRow($request);

        //StartLocalTransactionRequest
        $response = $this->otsClient->startLocalTransaction (array (
            'table_name' => 'TransactionTable',
            'key' => array(
                array('PK0', 0)
            )
        ));

        $updateRequest = array(
            'table_name' => 'TransactionTable',
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK0', 0),
                array('PK1', '1')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('attr0', 'new value')
                )
            ),
            'transaction_id' => $response['transaction_id']
        );
        $this->otsClient->updateRow($updateRequest);

        $this->otsClient->commitTransaction(array(
            'transaction_id' => $response['transaction_id']
        ));

        $response = $this->otsClient->getRow(
            array(
                'table_name' => 'TransactionTable',
                'primary_key' => array ( // 主键
                    array('PK0', 0),
                    array('PK1', '1')
                ),
                'max_versions' => 1
            )
        );

//        print json_encode ($response, JSON_PRETTY_PRINT);
        $this->assertEquals($response['attribute_columns'][0][0], 'attr0');
        $this->assertEquals($response['attribute_columns'][0][1], 'new value');
        $this->assertEquals($response['attribute_columns'][0][2], 'STRING');
    }
}

