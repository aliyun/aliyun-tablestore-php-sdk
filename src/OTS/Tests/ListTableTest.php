<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\ColumnTypeConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
    "myTable",
    "myTable1"
);
class listTableTest extends SDKTestBase {
    public function setup() {
        global $usedTables;
        $this->cleanUp ($usedTables);
    }
    
    /*
     * ListTableWith0Table
     * 在没有表的情况下 ListTable，期望返回0个Table Name
     */
    public function testListTableWith0Table() {
        $this->assertEmpty ($this->otsClient->listTable (array ()));
    }
    /*
     * ListTableWith1Table
     * 在有1个表的情况下 ListTable，期望返回1个Table Name。
     */
    public function testListTableWith1Table() {
        global $usedTables;
        
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[0],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_STRING,
                    "PK2" => ColumnTypeConst::CONST_INTEGER,
                    "PK3" => ColumnTypeConst::CONST_STRING,
                    "PK4" => ColumnTypeConst::CONST_INTEGER
                )
            ),
            
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        $this->otsClient->CreateTable ($tablebody);
        $table_name = array (
            $usedTables[0]
        );
        $this->assertEquals ($this->otsClient->listTable (array ()), $table_name);
    }
    
    /*
     * ListTableWith2Tables
     * 在有2个表的情况下 ListTable，期望返回2个Table Name。
     */
    public function testListTableWith2Tables() {
        global $usedTables;
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[0],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_STRING,
                    "PK2" => ColumnTypeConst::CONST_INTEGER,
                    "PK3" => ColumnTypeConst::CONST_STRING,
                    "PK4" => ColumnTypeConst::CONST_INTEGER
                )
            ),
            
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        $tablebody1 = array (
            "table_meta" => array (
                "table_name" => $usedTables[1],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_STRING,
                    "PK2" => ColumnTypeConst::CONST_INTEGER,
                    "PK3" => ColumnTypeConst::CONST_STRING,
                    "PK4" => ColumnTypeConst::CONST_INTEGER
                )
            ),
            
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        $this->otsClient->CreateTable ($tablebody);
        $this->otsClient->CreateTable ($tablebody1);
        $table_name = array (
            $usedTables[0],
            $usedTables[1]
        );
        $this->assertEquals ($this->otsClient->listTable (array ()), $table_name);
    }
    public function testListTable40Times() {
        for($i = 0; $i < 40; $i ++) {
            $this->otsClient->listTable (array () );
        }
    }
}

