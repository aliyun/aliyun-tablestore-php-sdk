<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class UpdatePerfTableTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable1',
        'myTable2',
        'myTable3',
        'myTable4',
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
        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[1],
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
        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[2],
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

        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[3],
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
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp ( self::$usedTables );
    }
    
    /*
     *
     * UpdateTable
     * 创建一个表，table_options为（-1，2, 86400），UpdateTable指定CU为（86400，3, 86420）
     * DescribeTable期望返回table_options为(86400, 3, 86420)。
     */
    public function testUpdateTable() {
        $name['table_name'] = self::$usedTables[0];
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'table_options' => array (
                'time_to_live' => 86400,
                'max_versions' => 3,
                'deviation_cell_version_in_sec' => 86420
            )
        );
        $this->otsClient->updateTable ($tablename);
        SDKTestBase::waitForAvoidFrequency();
        $description = $this->otsClient->describeTable ($name);
        $this->assertEquals ($tablename['table_options'], $description['table_options']);
    }
    
    /*
     * TimeToLiveOnly
     * 只更新 time_to_live，DescribeTable 校验返回符合预期。
     */
    public function testTimeToLiveOnly() {
        $name['table_name'] = self::$usedTables[1];
        $tablename = array (
            'table_name' => self::$usedTables[1],
            'table_options' => array (
                'time_to_live' => 86400
            )
        );
        $this->otsClient->updateTable ($tablename);
        SDKTestBase::waitForAvoidFrequency();
        $description = $this->otsClient->describeTable ($name);
        $this->assertEquals (86400, $description['table_options']['time_to_live']);
        $this->assertEquals (2, $description['table_options']['max_versions']);
        $this->assertEquals (86400, $description['table_options']['deviation_cell_version_in_sec']);
    }

    /*
     * MaxVersionOnly
     * 只更新 max_versions，DescribeTable 校验返回符合预期。
     */
    public function testMaxVersionOnly() {
        $name['table_name'] = self::$usedTables[2];
        $tablename = array (
            'table_name' => self::$usedTables[2],
            'table_options' => array (
                'max_versions' => 3
            )
        );
        $this->otsClient->updateTable ($tablename);
        SDKTestBase::waitForAvoidFrequency();
        $description = $this->otsClient->describeTable ($name);
        $this->assertEquals (-1, $description['table_options']['time_to_live']);
        $this->assertEquals (3, $description['table_options']['max_versions']);
        $this->assertEquals (86400, $description['table_options']['deviation_cell_version_in_sec']);
    }

    /*
     * DeviationOnly
     * 只更新 deviation_cell_version_in_sec，DescribeTable 校验返回符合预期。
     */
    public function testDeviationOnly() {
        $name['table_name'] = self::$usedTables[3];
        $tablename = array (
            'table_name' => self::$usedTables[3],
            'table_options' => array (
                'deviation_cell_version_in_sec' => 86420
            )
        );
        $this->otsClient->updateTable ($tablename);
        SDKTestBase::waitForAvoidFrequency();
        $description = $this->otsClient->describeTable ($name);
        $this->assertEquals (-1, $description['table_options']['time_to_live']);
        $this->assertEquals (2, $description['table_options']['max_versions']);
        $this->assertEquals (86420, $description['table_options']['deviation_cell_version_in_sec']);
    }
}
