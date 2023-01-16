<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class UpdateRowTest extends SDKTestBase {

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
        SDKTestBase::cleanUp (self::$usedTables);
    }

    /*
     *
     * PutOnlyInUpdateRow
     * UpdateRow包含4个属性列的put操作的情况。
     */
    public function testPutOnlyInUpdateRow() {
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('att1', 'Zhon'),
                    array('att2', 256),
                    array('att3', 'cc'),
                    array('att4', 123)
                )
            )
        );
        $this->otsClient->updateRow ($updateRow);
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'max_versions' => 1,
            'columns_to_get' => array ()
        );
        $getrow = $this->otsClient->getRow ($body);
        
        // print_r($updateRow['attribute_columns_to_put']);
        // print_r($getrow);
        // die;
        $this->assertColumnEquals($updateRow['update_of_attribute_columns']['PUT'], $getrow['attribute_columns'] );
    }
    
    /*
     * DeleteOnlyInUpdateRow
     * UpdateRow包含4个属性列的delete操作的情况。
     */
    public function testDeleteOnlyInUpdateRow() {
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('test1', 'name1'),
                array('test2', 256),
                array('test3', 'name2'),
                array('test4', 'name3')
            )
        );
        $this->otsClient->putRow ($tablename);
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'update_of_attribute_columns'=> array(
                'DELETE_ALL' => array (
                    'att1',
                    'att2',
                    'att3',
                    'att4'
                )
            )
        );
        $this->otsClient->updateRow ($updateRow);
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'max_versions' => 1,
            'columns_to_get' => array ()
        );
        $getrow = $this->otsClient->getRow ($body);
        // print_r($getrow);die;
        $this->assertArrayNotHasKey ('att1', $getrow['attribute_columns']);
        $this->assertArrayNotHasKey ('att2', $getrow['attribute_columns']);
        $this->assertArrayNotHasKey ('att3', $getrow['attribute_columns']);
        $this->assertArrayNotHasKey ('att4', $getrow['attribute_columns']);
    }
    
    /*
     * EmptyUpdateRow
     * UpdateRow没有包含任何操作的情况
     */
    public function testEmptyUpdateRow() {
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                )
            )
        );
        try {
            $this->otsClient->updateRow ($updateRow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'Attribute column is missing.';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }

    /*
     *
     * UpdateIncreaseInUpdateRow
     * UpdateRow包含1个属性列的Increment操作的情况。
     */
    public function testUpdateIncreaseInUpdateRow() {
        $origin = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'inc')
            ),
            'attribute_columns' => array (
                array('inc', 1)
            )
        );
        $this->otsClient->putRow ($origin);

        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'inc')
            ),
            'update_of_attribute_columns'=> array(
                'INCREMENT' => array (
                    array('inc', 1)
                )
            ),
            'return_content' => array(
                'return_type' => ReturnTypeConst::CONST_AFTER_MODIFY,
                'return_column_names' => array('inc')
            )
        );
        $response = $this->otsClient->updateRow ($updateRow);

        $this->assertEquals($response['attribute_columns'][0][0], 'inc');
        $this->assertEquals($response['attribute_columns'][0][1], 2);
    }
    
    /*
     * 4PutAnd4DeleteInUpdateRow
     * UpdateRow中包含4个put操作和4个delete操作的情况。
     * 同时测试空值的情形。
     */
    public function testPutAndDelete4InUpdateRow() {

        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('att5', 'cc'),
                    array('att6', 'Zhon'),
                    array('att7', 0.00),
                    array('att8', ''),
                    array('att9', 0)
                ),
                'DELETE_ALL' => array(
                    'att1',
                    'att2',
                    'att3',
                    'att4'
                )
            )
        );
        $this->otsClient->updateRow ($updateRow);
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            ),
            'max_versions' => 1
        );
        $getrow = $this->otsClient->getRow ($body);
        $this->assertColumnEquals($updateRow['update_of_attribute_columns']['PUT'], $getrow['attribute_columns'] );
        // $getrowlist = $this->otsClient->getRow($body);
    }

    /*
     * DeleteOneInUpdateRow
     * UpdateRow中包含4个put操作和4个delete操作的情况。
     */
    public function testDeleteOneInUpdateRow() {

        $timestamp = getMicroTime();

        // 1. PUT Row
        $tablename = array(
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array(
                array('PK1', 10315),
                array('PK2', 'a10315')
            ),
            'attribute_columns' => array(
                array('test1', 'name1', null, $timestamp-1),
                array('test2', 256),
                array('test3', 'name2'),
                array('test4', 'name3')
            )
        );
        $this->otsClient->putRow($tablename);

        // 2. update Row to increase version.
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 10315),
                array('PK2', 'a10315')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('att5', 'cc'),
                    array('att6', 'Zhon'),
                    array('att7', 1),
                    array('att8', 123),
                    array('test1','change the world', null,$timestamp)
                ),
                'DELETE_ALL' => array(
                    'att1',
                    'att2',
                    'att3',
                    'att4'
                )
            )
        );
        $this->otsClient->updateRow ($updateRow);

        // 3. Get row to verify
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 10315),
                array('PK2', 'a10315')
            ),
            'max_versions' => 2
        );
        $getrow = $this->otsClient->getRow ($body);


        // 4. continue to update.
        $expectColumn =  array(
            array('att5', 'cc'),
            array('att6', 'Zhon'),
            array('att7', 1),
            array('att8', 123),
            array('test1', 'change the world', null, $timestamp),
            array('test1', 'name1'),
            array('test2', 256),
            array('test3', 'name2'),
            array('test4', 'name3')
        );

        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);

//        $timestamp = $getrow['attribute_columns']['test1'][0][1];

        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 10315),
                array('PK2', 'a10315')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('att9', '99')
                ),
                'DELETE' => array(
                    array('test1', $timestamp)
                ),
                'DELETE_ALL' => array(
                    'att1',
                    'att2',
                    'att3',
                    'att4'
                )
            )
        );
        $this->otsClient->updateRow ($updateRow);

        // 5. check
        $getrow = $this->otsClient->getRow ($body);

        $expectColumn = array(
            array('att5', 'cc', ),
            array('att6', 'Zhon'),
            array('att7', 1),
            array('att8', 123),
            array('att9', '99'),
            array('test1', 'name1'),
            array('test2', 256),
            array('test3', 'name2'),
            array('test4', 'name3')
        );

        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);

        // $getrowlist = $this->otsClient->getRow($body);
    }
    
    /*
     * DuplicateDeleteInUpdateRow
     * UpdateRow中包含2个delete操作列名相同的情况，期望返回服务端错误 Duplicated attribute column name: 'att1' while updating row.
     * NOTE: 结果未定义, 所以注释掉本case.
     */
//    public function testDuplicateDeleteInUpdateRow() {
//        global $usedTables;
//        $updateRow = array (
//            'table_name' => $usedTables[0],
//            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
//            'primary_key' => array (
//                array('PK1', 3),
//                array('PK2', 'a3')
//            ),
//            'update_of_attribute_columns'=> array(
//                'PUT' => array (
//                    array('att1', 'cc')
//                ),
//                'DELETE_ALL' => array(
//                    'att1'
//
//                )
//            )
//        );
//        try {
//            $this->otsClient->updateRow ($updateRow);
//            $this->fail ('An expected exception has not been raised.');
//        } catch (\Aliyun\OTS\OTSServerException $exc) {
//            $c = 'Duplicated attribute column name: 'att1' while updating row.';
//            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
//        }
//    }
    
    /*
     * PutAndDelete1000InUpdateRow, limit count: 1024
     * UpdateRow中包含1000个put和1000个delete的情况，期望返回服务端错误 The number of attribute columns exceeds the limit.
     */
    public function testPutAndDelete1000InUpdateRow() {
        for($i = 1; $i < 1001; $i ++) {
            $put[] = array('a' . $i,  'cc' . $i);
            $delete[] = 'aa' . $i;
        }
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => $put,
                'DELETE_ALL' => $delete
            )
        );
        try {
            $this->otsClient->updateRow ($updateRow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'The number of attribute columns exceeds the limit';
            $this->assertContains($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * 上面测试已近包含
     * IgnoreConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为IGNORE，期望操作成功。
     * IgnoreConditionWhenRowExist
     * 测试行不存在的条件下，写操作的Condition为IGNORE，期望操作成功。
     */
    
    // ============================================================================//
    /*
     * ExpectExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_EXIST，期望服务端返回 Invalid Condition。
     */
    public function testExpectExistConditionWhenRowNotExist() {
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'primary_key' => array (
                array('PK1', 30),
                array('PK2', 'a30')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('att1', 'cc')
                ),
                'DELETE_ALL' => array (
                    'att2'
                )
            )
        );
        // print_r($this->otsClient->updateRow($updateRow));die;
        try {
            $this->otsClient->updateRow ($updateRow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'Condition check failed.';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * ExpectExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_EXIST，期望操作成功。
     */
    public function testExpectExistConditionWhenRowExist() {
        $currentTime = getMicroTime();
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'attribute_columns' => array (
                array('test1', 'name1', null, $currentTime),
                array('test2', 256, null, $currentTime),
                array('test3', 'name2', null, $currentTime),
                array('test4', 'name3', null, $currentTime)
            )
        );
        $this->otsClient->putRow ($tablename);
        $updateRow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('test1', 'cc', null, $currentTime+1)
                ),
                'DELETE_ALL' => array (
                    'att2'
                )
            )
        );
        $this->otsClient->updateRow ($updateRow);
        // print_r($a);die;
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'max_versions' => 1,
            'columns_to_get' => array ()
        );
        $c = $this->otsClient->getRow ($body);

        $expectColumn = array(
            array('test1', 'cc', 'STRING', $currentTime+1),
            array('test2', 256, 'INTEGER', $currentTime),
            array('test3', 'name2', 'STRING', $currentTime),
            array('test4', 'name3', 'STRING', $currentTime)
        );

        $this->assertColumnEquals($expectColumn, $c['attribute_columns'] );
    }
    
    /**
     * 测试在使用ColumnCondition的情况下，更新数据行是否成功。
     */
    public function testUpdateRowWithColumnCondition() {
        $put_query = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'attribute_columns' => array (
                array('test1', 'name1'),
                array('test2', 256),
                array('test3', 'name2'),
                array('test4', 'name3')
            )
        );
        $this->otsClient->putRow ($put_query);
        
        $update_query = array (
            'table_name' => self::$usedTables[0],
            'condition' => array (
                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                'column_condition' => array (
                    'column_name' => 'test1',
                    'value' => 'name1',
                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                )
            ),
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('test5', 'cc')
                )
            )
        );
        $this->otsClient->updateRow ($update_query);
        
        $get_query = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'columns_to_get' => array (
                'test1',
                'test2',
                'test3',
                'test4',
                'test5'
            ),
            'max_versions' => 1
        );
        $get_row_res = $this->otsClient->getRow ($get_query);
        $expectColumn = array(
            array('test1', 'name1'),
            array('test2', 256),
            array('test3', 'name2'),
            array('test4', 'name3'),
            array('test5', 'cc')
        );
        $this->assertColumnEquals($expectColumn, $get_row_res['attribute_columns']);
        
        $update_query2 = array (
            'table_name' => self::$usedTables[0],
            'condition' => array (
                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                'column_condition' => array (
                    'column_name' => 'test1',
                    'value' => 'name1',
                    'comparator' => ComparatorTypeConst::CONST_NOT_EQUAL
                )
            ),
            'primary_key' => array (
                array('PK1', 100),
                array('PK2', 'a100')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('test6', 'ddcc')
                )
            )
        );
        try {
            $this->otsClient->updateRow ($update_query2);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'Condition check failed.';
            $this->assertEquals ( $c, $exc->getOTSErrorMessage () );
        }
    }
}

