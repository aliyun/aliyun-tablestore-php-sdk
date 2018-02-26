<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\ColumnTypeConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
    "myTable1",
    "myTable2",
    "myTable3"
);

SDKTestBase::cleanUp ($usedTables);
SDKTestBase::createInitialTable (array (
    "table_meta" => array (
        "table_name" => $usedTables[0],
        "primary_key_schema" => array (
            "PK1" => ColumnTypeConst::CONST_INTEGER,
            "PK2" => ColumnTypeConst::CONST_STRING
        )
    ),
    "reserved_throughput" => array (
        "capacity_unit" => array (
            "read" => 10,
            "write" => 20
        )
    )
));
SDKTestBase::createInitialTable (array (
    "table_meta" => array (
        "table_name" => $usedTables[1],
        "primary_key_schema" => array (
            "PK1" => ColumnTypeConst::CONST_INTEGER,
            "PK2" => ColumnTypeConst::CONST_STRING
        )
    ),
    "reserved_throughput" => array (
        "capacity_unit" => array (
            "read" => 10,
            "write" => 20
        )
    )
));
SDKTestBase::createInitialTable (array (
    "table_meta" => array (
        "table_name" => $usedTables[2],
        "primary_key_schema" => array (
            "PK1" => ColumnTypeConst::CONST_INTEGER,
            "PK2" => ColumnTypeConst::CONST_STRING
        )
    ),
    "reserved_throughput" => array (
        "capacity_unit" => array (
            "read" => 10,
            "write" => 20
        )
    )
));
SDKTestBase::waitForCUAdjustmentInterval ();
class UpdateTableTest extends SDKTestBase {
    
    /*
     *
     * UpdateTable
     * 创建一个表，CU为（10，20），UpdateTable指定CU为（5，30），DescribeTable期望返回CU为(5, 30)。
     */
    public function testUpdateTable() {
        global $usedTables;
        $name['table_name'] = $usedTables[0];
        $tablename = array (
            "table_name" => $usedTables[0],
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 5,
                    "write" => 30
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
     */
    public function testCUReadOnly() {
        global $usedTables;
        $name['table_name'] = $usedTables[1];
        $tablename = array (
            "table_name" => $usedTables[1],
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 100
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
     */
    public function testCUWriteOnly() {
        global $usedTables;
        $name['table_name'] = $usedTables[2];
        $tablename = array (
            "table_name" => $usedTables[2],
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "write" => 300
                )
            )
        );
        $this->otsClient->updateTable ($tablename);
        $capacity_unit = $this->otsClient->describeTable ($name);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit']['read'], 10);
        $this->assertEquals ($capacity_unit['capacity_unit_details']['capacity_unit'] ['write'], 300 );
    }
}

