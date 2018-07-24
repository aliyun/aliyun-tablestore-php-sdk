<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class DeleteRowTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable'
    );

    public static function setUpBeforeClass()
    {
        SDKTestBase::cleanUp (self::$usedTables);
        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
            )
        ));
        SDKTestBase::waitForTableReady ();
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp ( self::$usedTables );
    }
    
    /*
     *
     * TableNameOfZeroLength
     * 创建一个表，并删除，ListTable期望返回0个TableName。
     */
    public function testTableNameOfZeroLength() {
        $deleterow = array (
            'table_name' => '',
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * 5ColumnInPK
     * 和表主键不一致，指定5个主键
     */
    public function testColumnInPK() {
        $deleterow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'aaa'),
                array('PK2', 'cc'),
                array('PK3', 'ccd'),
                array('PK4', 'cds'),
                array('PK5', '11s')
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'The number of primary key columns must be in range: [1, 4].';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * ExpectExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_EXIST，期望服务端返回 Invalid Condition。
     */
    public function testExpectExistConditionWhenRowNotExist() {
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 'asds'),
                array('att2', 'sdsd')
            )
        );
        $this->otsClient->putRow ($tablename);
        $deleterow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2')
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Condition check failed.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * ExpectExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_EXIST，期望操作成功。
     */
    public function testExpectExistConditionWhenRowExist() {
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 'asds'),
                array('att2', 'sdsd')
            )
        );
        $this->otsClient->putRow ($tablename);
        $deleterow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            )
        );
        $this->otsClient->deleteRow ($deleterow);
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'columns_to_get' => array (),
            'max_versions' => 1
        );
        $getrow = $this->otsClient->getRow ($body);
        // print_r($getrow);die;
        $this->assertEmpty ($getrow['primary_key']);
        $this->assertEmpty ($getrow['attribute_columns']);
    }

    // DeleteRow 只可以取两个值，IGNORE，EXPECT_EXIST，所以原来的下面两个测试作废
    /*
     *
     * ExpectNotExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_NOT_EXIST
     *
     */
//    public function testExpectNotExistConditionWhenRowNotExist() {
//        $deleterow = array (
//            'table_name' => self::$usedTables[0],
//            'condition' => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
//            'primary_key' => array (
//                array('PK1', 1),
//                array('PK2', 'a1')
//            )
//        );
//        try {
//            $this->otsClient->deleteRow ($deleterow);
//            $this->fail ('An expected exception has not been raised.');
//        } catch (\Aliyun\OTS\OTSServerException $exc) {
//            $c = 'Invalid condition: EXPECT_NOT_EXIST while deleting row.';
//            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
//        }
//    }
    /*
     * ExpectNotExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_NOT_EXIST
     */
//    public function testExpectNotExistConditionWhenRowExist() {
//        $tablename = array (
//            'table_name' => self::$usedTables[0],
//            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
//            'primary_key' => array (
//                array('PK1', 1),
//                array('PK2', 'a1')
//            ),
//            'attribute_columns' => array (
//                array('att1', 'asds'),
//                array('att2', 'sdsd')
//            )
//        );
//        $this->otsClient->putRow ($tablename);
//        $deleterow = array (
//            'table_name' => self::$usedTables[0],
//            'condition' => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
//            'primary_key' => array (
//                array('PK1', 1),
//                array('PK2', 'a1')
//            )
//        );
//        try {
//            $this->otsClient->deleteRow ($deleterow);
//            $this->fail ('An expected exception has not been raised.');
//        } catch (\Aliyun\OTS\OTSServerException $exc) {
//            $c = 'Invalid condition: EXPECT_NOT_EXIST while deleting row.';
//            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
//        }
//    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件下，删除数据行是否成功。
     */
    public function testDeleteRowWithColumnCondition() {
        $put_query = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 'asds'),
                array('att2', 'sdsd')
            )
        );
        $this->otsClient->putRow ($put_query);
        
        $delete_query = array (
            'table_name' => self::$usedTables[0],
            'condition' => array (
                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                'column_condition' => array (
                    'column_name' => 'attr1',
                    'value' => 'asds',
                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                )
            ),
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            )
        );
        $this->otsClient->deleteRow ($delete_query);
        
        $get_query = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'columns_to_get' => array (
                'attr1',
                'attr2'
            ),
            'max_versions' => 1
        );
        $get_row_res = $this->otsClient->getRow ($get_query);
        $this->assertEquals (count ($get_row_res['attribute_columns']), 0);
        
        $put_query2 = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 'asds'),
                array('att2', 'sdsd')
            )
        );
        $this->otsClient->putRow ($put_query2);
        
        $delete_query2 = array (
            'table_name' => self::$usedTables[0],
            'condition' => array (
                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                'column_condition' => array (
                    'column_name' => 'att1',
                    'value' => 'asdsddd',
                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                )
            ),
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            )
        );
        try {
            $this->otsClient->deleteRow ($delete_query2);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $a = $exc->getMessage ();
            $c = 'Condition check failed.';
            $this->assertContains ( $c, $a );
        }
    }
}

