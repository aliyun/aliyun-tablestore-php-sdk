<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\DirectionConst;
use Aliyun\OTS\Consts\OperationTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyOptionConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class PKAutoIncrTest extends SDKTestBase {

    private static $usedTables = array (
        'OTSPkAutoIncrSimpleExample'
    );

    public static function setUpBeforeClass()
    {
        SDKTestBase::cleanUp ( self::$usedTables );
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp ( self::$usedTables );
    }

    /*
     *
     * CreateTable
     * 测试设置主键列自增功能正常
     */
    public function testPKAuto() {

        $this->createTable();

        $primaryKeys = $this->putRow();

        $this->getRow($primaryKeys);

        $this->updateRow($primaryKeys);

        $this->getRowWithFilter($primaryKeys);

        $this->updateRowWithCondition($primaryKeys);

        $this->deleteRow($primaryKeys);

        $this->putRow();

        $this->putRow();

        $primaryKeys = $this->getRange();

        $this->batchWriteRow($primaryKeys);

    }

    public function createTable()
    {
        $tablebody = array(
            'table_meta' => array(
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('gid', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('uid', PrimaryKeyTypeConst::CONST_INTEGER, PrimaryKeyOptionConst::CONST_PK_AUTO_INCR)
                )
            ),
            'reserved_throughput' => array(
                'capacity_unit' => array(
                    'read' => 0,
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 3,
                'deviation_cell_version_in_sec' => 86400
            )
        );
        $this->otsClient->createTable($tablebody);
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];

        $teturn = array(
            'table_name' => $tablebody['table_meta']['table_name'],
            'primary_key_schema' => array(
                array('gid', PrimaryKeyTypeConst::CONST_INTEGER),
                array('uid', PrimaryKeyTypeConst::CONST_INTEGER, PrimaryKeyOptionConst::CONST_PK_AUTO_INCR)
            )
        );
        $table_meta = $this->otsClient->describeTable($tablename);
        $this->assertEquals($teturn, $table_meta['table_meta']);

        SDKTestBase::waitForTableReady();
    }

    public function putRow()
    {
        $row = array(
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array(
                array('gid',  1),
                array('uid', null, PrimaryKeyTypeConst::CONST_PK_AUTO_INCR)
            ),

            'attribute_columns' => array(
                array('name', 'John'),
                array('age', 20),
                array('address', 'Alibaba'),
                array('product', 'OTS'),
                array('married', false)
            ),
            'return_content' => array(
                'return_type' => ReturnTypeConst::CONST_PK
            )
        );
        $ret = $this->otsClient->putRow($row);

        $this->assertTrue(isset($ret['primary_key']));
        $primaryKeys = $ret['primary_key'];
        return $primaryKeys;
    }


    public function getRow($primaryKeys)
    {
        $rowToGet = array(
            'table_name' => self::$usedTables[0],
            'primary_key' => $primaryKeys,
            'max_versions' => 1
        );

        $ret = $this->otsClient->getRow($rowToGet);
        $expect = array(
            array('address', 'Alibaba', ),
            array('age', 20),
            array('married', false),
            array('name', 'John'),
            array('product', 'OTS')
        );

        $this->assertColumnEquals($expect, $ret['attribute_columns']);
    }

    private function updateRow($primaryKeys)
    {
        $rowToChange = array(
            'table_name' => self::$usedTables[0],
            'primary_key' => $primaryKeys,
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('language', 'Chinese'),
                    array('address', 'aliyun')
                ),
                'DELETE' => array(),
                'DELETE_ALL' => array(
                    'married'
                )
            ),
            'return_content' => array(
                'return_type' => ReturnTypeConst::CONST_PK
            )
        );
        $this->otsClient->updateRow ($rowToChange);
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => $primaryKeys,
            'columns_to_get' => array (),
            'max_versions' => 2
        );
        $getrow = $this->otsClient->getRow ($body);
        $this->assertEquals($primaryKeys, $getrow['primary_key']);
        $expect = array(
            array('address', 'aliyun'),
            array('address', 'Alibaba'),
            array('age', 20),
            array('language', 'Chinese'),
            array('name', 'John'),
            array('product', 'OTS')
        );

        $this->assertColumnEquals($expect, $getrow['attribute_columns']);
    }

    private function getRowWithFilter($primaryKeys)
    {
        $querybody = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => $primaryKeys,
            'columns_to_get' => array (),
            'column_filter' => array (
                'column_name' => 'name',
                'value' => 'John',
                'comparator' => ComparatorTypeConst::CONST_EQUAL,
                'pass_if_missing' => false
            ),
            'max_versions' => 1,
            'return_content' => array(
                'return_type' => ReturnTypeConst::CONST_PK
            )
        );
        $getrowres = $this->otsClient->getRow ($querybody);
        $this->assertEquals($primaryKeys, $getrowres['primary_key']);
        $expect = array(
            array('address', 'aliyun'),
            array('age', 20),
            array('language', 'Chinese'),
            array('name', 'John'),
            array('product', 'OTS')
        );

        $this->assertColumnEquals($expect, $getrowres['attribute_columns']);

    }

    private function updateRowWithCondition($primaryKeys)
    {
        $update_query = array (
            'table_name' => self::$usedTables[0],
            'condition' => array (
                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                'column_condition' => array (
                    'column_name' => 'name',
                    'value' => 'John',
                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                )
            ),
            'primary_key' => $primaryKeys,
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('address', 'Alibaba Aliyun')
                )
            ),
            'return_content' => array(
                'return_type' => ReturnTypeConst::CONST_PK
            )
        );
        $updateRowres = $this->otsClient->updateRow($update_query);
        $this->assertEquals($primaryKeys, $updateRowres['primary_key']);

        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => $primaryKeys,
            'columns_to_get' => array (),
            'max_versions' => 3
        );
        $getrow = $this->otsClient->getRow ($body);
        $this->assertEquals($primaryKeys, $getrow['primary_key']);

        $expect = array(
            array('address', 'Alibaba Aliyun', ),
            array('address', 'aliyun'),
            array('address', 'Alibaba'),
            array('age', 20),
            array('language', 'Chinese'),
            array('name', 'John'),
            array('product', 'OTS')
        );
        $this->assertColumnEquals($expect, $getrow['attribute_columns']);
    }

    private function getRange()
    {
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 10,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('gid', null, PrimaryKeyTypeConst::CONST_INF_MIN),
                array('uid', null, PrimaryKeyTypeConst::CONST_INF_MIN)
            ),
            'exclusive_end_primary_key' => array (
                array('gid', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('uid', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $getRows = $this->otsClient->getRange($getRange);
        $this->assertEquals(2, count($getRows['rows']));
        return array($getRows['rows'][0]['primary_key'],$getRows['rows'][1]['primary_key'] );

    }

    private function batchWriteRow($primaryKeys)
    {
        $batchWrite1 = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => $primaryKeys[0],
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('name', 'Peter')
                                ),
                                'DELETE_ALL' => array(
                                    'married'
                                )
                            ),
                            'return_content' => array(
                                'return_type' => ReturnTypeConst::CONST_PK
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => $primaryKeys[1],
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('name', 'Jane')
                                ),
                                'DELETE_ALL' => array(
                                    'married'
                                )
                            ),
                            'return_content' => array(
                                'return_type' => ReturnTypeConst::CONST_PK
                            )
                        )
                    )
                )
            )
        );
        $writeRows = $this->otsClient->batchWriteRow ($batchWrite1);
        $this->assertEquals(2, count($writeRows['tables'][0]['rows']));
        $this->assertNotNull($writeRows['tables'][0]['rows'][0]);
        $this->assertNotNull($writeRows['tables'][0]['rows'][1]);
        $this->assertEquals($primaryKeys[0], $writeRows['tables'][0]['rows'][0]['primary_key']);
        $this->assertEquals($primaryKeys[1], $writeRows['tables'][0]['rows'][1]['primary_key']);
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        $primaryKeys[0],
                        $primaryKeys[1]
                    )
                )
            )
        );

        $getRows = $this->otsClient->batchGetRow ($batchGet);
        $this->assertEquals(2, count($getRows['tables'][0]['rows']));
        $this->assertNotNull($getRows['tables'][0]['rows'][0]);
        $this->assertNotNull($getRows['tables'][0]['rows'][1]);
        $this->assertEquals($primaryKeys[0], $getRows['tables'][0]['rows'][0]['primary_key']);
        $this->assertEquals($primaryKeys[1], $getRows['tables'][0]['rows'][1]['primary_key']);
        $this->assertEquals('Peter', $getRows['tables'][0]['rows'][0]['attribute_columns'][2][1]);
        $this->assertEquals('Jane', $getRows['tables'][0]['rows'][1]['attribute_columns'][2][1]);

    }

    private function deleteRow($primaryKeys)
    {
        $deleterow = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'primary_key' => $primaryKeys,
            'return_content' => array(
                'return_type' => ReturnTypeConst::CONST_PK
            )
        );

        $ret = $this->otsClient->deleteRow($deleterow);
        $this->assertEquals($primaryKeys, $ret['primary_key']);
    }
}

