<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\LogicalOperatorConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class TimeRangeTest extends SDKTestBase {

    private static $usedTables = array (
        'TimeRangeTable'
    );

    public static function setUpBeforeClass()
    {
        SDKTestBase::cleanUp (self::$usedTables);
        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
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
                'time_to_live' => -1,    // 数据永不过期
                'max_versions' => 10,    // 设置max_version为10
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
     * GetRowWithStartEndTime
     * 先PutRow包含10个version的数据，然后通过时间指定其中4个version用来读取。
     */
    public function testGetRowWithStartEndTime() {

        $baseTime = getMicroTime();

        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 'a1'),
                array('PK2', 'b1')
            ),
            'time_range' => array(
                'start_time' => $baseTime+1,
                'end_time' => $baseTime+5,
            ),
            'columns_to_get' => array (
                'MultiVersion'
            )
        );
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'a1'),
                array('PK2', 'b1')
            ),
            'attribute_columns' => array (
                array('MultiVersion', 'version1', null, $baseTime),
                array('MultiVersion', 'version2', null, $baseTime+1),
                array('MultiVersion', 'version3', null, $baseTime+2),
                array('MultiVersion', 'version4', null, $baseTime+3),
                array('MultiVersion', 'version5', null, $baseTime+4),
                array('MultiVersion', 'version6', null, $baseTime+5),
                array('MultiVersion', 'version7', null, $baseTime+6),
                array('MultiVersion', 'version8', null, $baseTime+7),
                array('MultiVersion', 'version9', null, $baseTime+8),
                array('MultiVersion', 'version10', null, $baseTime+9),
                array('MultiVersion', 'version11', null, $baseTime+10)
            )
        );
        $expectColumn = array(
            array('MultiVersion', 'version5', null, $baseTime+4),
            array('MultiVersion', 'version4', null, $baseTime+3),
            array('MultiVersion', 'version3', null, $baseTime+2),
            array('MultiVersion', 'version2', null, $baseTime+1)
        );

        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);
    }

    /*
     * GetRowWithSpecificTime
     * 先PutRow包含10个version的数据，然后通过时间指定其中1个具体version用来读取。
     */
    public function testGetRowWithSpecificTime() {

        $baseTime = getMicroTime();

        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 'a2'),
                array('PK2', 'b2')
            ),
            'time_range' => array(
                'specific_time' => $baseTime+4
            ),
            'columns_to_get' => array (
                'MultiVersion'
            )
        );
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'a2'),
                array('PK2', 'b2')
            ),
            'attribute_columns' => array (
                array('MultiVersion', 'version1', null, $baseTime),
                array('MultiVersion', 'version2', null, $baseTime+1),
                array('MultiVersion', 'version3', null, $baseTime+2),
                array('MultiVersion', 'version4', null, $baseTime+3),
                array('MultiVersion', 'version5', null, $baseTime+4),
                array('MultiVersion', 'version6', null, $baseTime+5),
                array('MultiVersion', 'version7', null, $baseTime+6),
                array('MultiVersion', 'version8', null, $baseTime+7),
                array('MultiVersion', 'version9', null, $baseTime+8),
                array('MultiVersion', 'version10', null, $baseTime+9),
                array('MultiVersion', 'version11', null, $baseTime+10)
            )
        );
        $expectColumn = array(
            array('MultiVersion', 'version5', null, $baseTime+4),
        );

        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);
    }

    /*
     * GetRowWithStartTimeAndMaxversion
     * 先PutRow包含10个version的数据，然后通过时间和max_versions指定其中3个具体version用来读取。
     * 期望返回的是从start_time开始的max_versions个结果
     */
    public function testGetRowWithStartTimeAndMaxversion() {

        $baseTime = getMicroTime();

        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 'a3'),
                array('PK2', 'b3')
            ),
            'time_range' => array(
                'start_time' => $baseTime+2,
                'end_time' => $baseTime+5,
            ),
            'max_versions' => 3,
            'columns_to_get' => array (
                'MultiVersion'
            )
        );
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'a3'),
                array('PK2', 'b3')
            ),
            'attribute_columns' => array (
                array('MultiVersion', 'version1', null, $baseTime),
                array('MultiVersion', 'version2', null, $baseTime+1),
                array('MultiVersion', 'version3', null, $baseTime+2),
                array('MultiVersion', 'version4', null, $baseTime+3),
                array('MultiVersion', 'version5', null, $baseTime+4),
                array('MultiVersion', 'version6', null, $baseTime+5),
                array('MultiVersion', 'version7', null, $baseTime+6),
                array('MultiVersion', 'version8', null, $baseTime+7),
                array('MultiVersion', 'version9', null, $baseTime+8),
                array('MultiVersion', 'version10', null, $baseTime+9),
                array('MultiVersion', 'version11', null, $baseTime+10)
            )
        );
        $expectColumn = array(
            array('MultiVersion', 'version5', null, $baseTime+4),
            array('MultiVersion', 'version4', null, $baseTime+3),
            array('MultiVersion', 'version3', null, $baseTime+2),
        );

        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);
    }
}

