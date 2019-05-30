<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\DefinedColumnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class GlobalIndexRead1Test extends SDKTestBase {

    private static $tableName = 'testGlobalTableName';
    private static $indexName1 = 'testGlobalTableWithIndex1';
    private static $indexName2 = 'testGlobalTableWithIndex2';
    public static function setUpBeforeClass()
    {
        self::createTable();
        self::insertData();
        self::waitForAvoidFrequency();
    }

    public static function tearDownAfterClass()
    {
        self::cleanUpGlobalIndex(self::$tableName);
        self::cleanUp(array(self::$tableName));
    }

    public function testGetRowByIndex() {
        $request = array (
            'table_name' => self::$indexName1,
            'primary_key' => array (
                array('col1', 'keyword'),
                array('PK0', 1),
                array('PK1', 'global')
            ),
            'max_versions' => 1,
            'columns_to_get' => array ('col2')
        );
        $response = $this->otsClient->getRow($request);
//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertEquals($response['attribute_columns'][0][0], 'col2');
        $this->assertEquals($response['attribute_columns'][0][1], 1);
        $this->assertEquals($response['attribute_columns'][0][2], 'INTEGER');

        $request = array (
            'table_name' => self::$indexName2,
            'primary_key' => array (
                array('PK1', 'global'),
                array('PK0', 2)
            ),
            'max_versions' => 1,
            'columns_to_get' => array ('col2')
        );
        $response = $this->otsClient->getRow($request);
//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertEquals($response['attribute_columns'][0][0], 'col2');
        $this->assertEquals($response['attribute_columns'][0][1], 2);
        $this->assertEquals($response['attribute_columns'][0][2], 'INTEGER');
    }

    private static function createTable() {
        $request = array (
            'table_meta' => array (
                'table_name' => self::$tableName, // 表名为 testGlobalTableName
                'primary_key_schema' => array (
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
                ),
                'defined_column' => array(
                    array('col1', DefinedColumnTypeConst::DCT_STRING),
                    array('col2', DefinedColumnTypeConst::DCT_INTEGER)
                )
            ),

            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
                'max_versions' => 1,    // 最大数据版本
                'deviation_cell_version_in_sec' => 86400  // 数据有效版本偏差，单位秒
            ),
            'index_metas' => array(
                array(
                    'name' => self::$indexName1,
                    'primary_key' => array('col1'),
                    'defined_column' => array('col2')
                ),
                array(
                    'name' => self::$indexName2,
                    'primary_key' => array('PK1'),
                    'defined_column' => array('col1', 'col2')
                )
            )
        );
        SDKTestBase::createInitialTable($request);
    }
    private static function insertData() {
        for ($i = 0; $i < 5; $i++) {
            $request = array(
                'table_name' => self::$tableName,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array ( // 主键
                    array('PK0', $i),
                    array('PK1', 'global')
                ),
                'attribute_columns' => array(
                    array('col1', 'keyword'),
                    array('col2', $i)
                )
            );

            SDKTestBase::putInitialData($request);
        }
    }
}

