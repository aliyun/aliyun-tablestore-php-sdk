<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// NOTE:此测试对容量型实例无效,只对高性能实例有效
class UpdatePerfTableTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable1',
        'myTable2',
        'myTable3'
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
                    'read' => 10,
                    'write' => 20
                )
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
                    'read' => 10,
                    'write' => 20
                )
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
                    'read' => 10,
                    'write' => 20
                )
            )
        ));
        SDKTestBase::waitForCUAdjustmentInterval ();
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp ( self::$usedTables );
    }
    
    /*
     *
     * UpdateTable
     * 创建一个表，CU为（10，20），UpdateTable指定CU为（5，30），DescribeTable期望返回CU为(5, 30)。
     * NOTE:对容量型实例无效,只对高性能实例有效
     */
    public function testUpdateTable() {
        $name['table_name'] = self::$usedTables[0];
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 5,
                    'write' => 30
                )
            )
        );
        $this->otsClient->updateTable ($tablename);
        
        $capacity_unit = $this->otsClient->describeTable ($name);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit'], $tablename['reserved_throughput']['capacity_unit']);
    }
    
    /*
     * CUReadOnly
     * 只更新 Read CU，DescribeTable 校验返回符合预期。
     * NOTE:对容量型实例无效,只对高性能实例有效
     */
    public function testCUReadOnly() {
        $name['table_name'] = self::$usedTables[1];
        $tablename = array (
            'table_name' => self::$usedTables[1],
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 100
                )
            )
        );
        $this->otsClient->updateTable ($tablename);
        
        $capacity_unit = $this->otsClient->describeTable ($name);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit']['read'], 100);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit']['write'], 20);
    }
    /*
     * CUWriteOnly
     * 只更新 Write CU，DescribeTable 校验返回符合预期。
     * NOTE:对容量型实例无效,只对高性能实例有效
     */
    public function testCUWriteOnly() {
        $name['table_name'] = self::$usedTables[2];
        $tablename = array (
            'table_name' => self::$usedTables[2],
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'write' => 300
                )
            )
        );
        $this->otsClient->updateTable ($tablename);
        $capacity_unit = $this->otsClient->describeTable ($name);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit']['read'], 10);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit'] ['write'], 300 );
    }

    public function testAllowUpdate() {
        $name['table_name'] = self::$usedTables[2];
        $describeTableResp = $this->otsClient->describeTable ($name);
        $this->assertTrue($describeTableResp['table_options']['allow_update']);

        $tablename = array (
            'table_name' => self::$usedTables[2],
            'table_options' => array(
                'allow_update' => false
            )
        );
        $this->otsClient->updateTable ($tablename);
        $describeTableResp = $this->otsClient->describeTable ($name);
        $this->assertFalse($describeTableResp['table_options']['allow_update']);
    }
}
