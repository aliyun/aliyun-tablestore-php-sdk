<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\ColumnTypeConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
    "test5",
    "test"
);

SDKTestBase::cleanUp ($usedTables);
class DescribeTableTest extends SDKTestBase {
    public function setup() {
       global $usedTables;
       $this->cleanUp ($usedTables);
    }
    
    /*
     * IntegerPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 INTEGER 的情况。
     */
    public function testIntegerPKInSchema() {
        global $usedTables;
        
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[0],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_INTEGER,
                    "PK2" => ColumnTypeConst::CONST_INTEGER
                )
            ),
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array (
                "PK1" => ColumnTypeConst::CONST_INTEGER,
                "PK2" => ColumnTypeConst::CONST_INTEGER
            )
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * StringPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 STRING 的情况。
     */
    public function testStringPKInSchema() {
        global $usedTables;
        
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[0],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_STRING,
                    "PK2" => ColumnTypeConst::CONST_STRING
                )
            ),
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array (
                "PK1" => ColumnTypeConst::CONST_STRING,
                "PK2" => ColumnTypeConst::CONST_STRING
            )
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * InvalidPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，
     * 类型为 DOUBLE / BOOLEAN / BINARY / INF_MIN / INF_MAX 的情况，期望返回错误
     */
    public function testInvalidPKInSchema() {
        global $usedTables;
        
        $invalidTypes = array (
            ColumnTypeConst::CONST_DOUBLE,
            ColumnTypeConst::CONST_BOOLEAN,
            ColumnTypeConst::CONST_INF_MIN,
            ColumnTypeConst::CONST_INF_MAX
        );
        
        foreach ($invalidTypes as $type) {
            $request = array (
                "table_meta" => array (
                    "table_name" => $usedTables[1],
                    "primary_key_schema" => array (
                        "PK1" => $type
                    )
                ),
                "reserved_throughput" => array (
                    "capacity_unit" => array (
                        "read" => 0,
                        "write" => 0
                    )
                )
            );
            
            try {
                $this->otsClient->createTable ($request);
                $this->fail ('An expected exception has not been raised.');
            } catch (\Aliyun\OTS\OTSServerException $e) {
                $c = "$type is an invalid type for the primary key.";
                $this->assertEquals ($c, $e->getOTSErrorMessage ());
            }
        }
    }
}

