<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class listTableTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable',
        'myTable1'
    );

    public function setUp(): void
    {
        $this->cleanUp (self::$usedTables);
    }

    public function tearDown(): void
    {
        $this->cleanUp (self::$usedTables);
    }

    /*
     * ListTableWith0Table
     * 在没有表的情况下 ListTable，期望返回0个Table Name
     */
    public function testListTableWith0Table() {
        $tableList = $this->otsClient->listTable (array ());
        foreach (self::$usedTables as $tableName) {
            $this->assertNotContains ($tableName, $tableList);
        }
    }
    /*
     * ListTableWith1Table
     * 在有1个表的情况下 ListTable，期望返回1个Table Name。
     */
    public function testListTableWith1Table() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $this->otsClient->CreateTable ($tablebody);
        $tableList = $this->otsClient->listTable (array ());
        $this->assertContains (self::$usedTables[0], $tableList);
    }
    
    /*
     * ListTableWith2Tables
     * 在有2个表的情况下 ListTable，期望返回2个Table Name。
     */
    public function testListTableWith2Tables() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $tablebody1 = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[1],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $this->otsClient->CreateTable ($tablebody);
        $this->otsClient->CreateTable ($tablebody1);
        $tableList = $this->otsClient->listTable (array ());
        $this->assertContains (self::$usedTables[0], $tableList);
        $this->assertContains (self::$usedTables[1], $tableList);
    }
    public function testListTable40Times() {
        for($i = 0; $i < 40; $i ++) {
            $this->otsClient->listTable (array () );
        }
        // add assert for higher phpunit require else Fatal [This test did not perform any assertions]
        $this->assertTrue(true);
    }
}

