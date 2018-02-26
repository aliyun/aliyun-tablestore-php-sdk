<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\ColumnTypeConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
        "myTable",
        "test2",
        "test3",
        "test4",
        "test",
        "test5"
);

class CreateTableTest extends SDKTestBase {
    public function setup() {
        global $usedTables;
        $table_name = $usedTables;
        for($i = 0; $i < count ($table_name); $i ++) {
            $request = array (
                    "table_name" => $table_name[$i]
            );
            try {
                $this->otsClient->deleteTable ($request);
            } catch (\Aliyun\OTS\OTSServerException $exc) {
                if ($exc->getOTSErrorCode() == 'OTSObjectNotExist')
                    continue;
            }
        }
    }
    
    /*
     *
     * CreateTable
     * 创建一个表，然后DescribeTable校验TableMeta和ReservedThroughput与建表时的参数一致
     */
    public function testCreateTable() {
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
        $this->otsClient->createTable ($tablebody);
        $tablename = array (
            $usedTables[0]
        );
        // $tablename['mytable'] = 111;
        $this->assertEquals ($this->otsClient->listTable (array ()), $tablename);
        // $this->assertContains();
        $table_name['table_name'] = $usedTables[0];
        $teturn = array (
            "table_name" => $usedTables[0],
            "primary_key_schema" => array (
                "PK1" => ColumnTypeConst::CONST_STRING,
                "PK2" => ColumnTypeConst::CONST_INTEGER,
                "PK3" => ColumnTypeConst::CONST_STRING,
                "PK4" => ColumnTypeConst::CONST_INTEGER
            )
        );
        $table_meta = $this->otsClient->describeTable ($table_name);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
        // $this->otsClient->deleteTable($table_name);
    }
    
    /*
     * TableNameOfZeroLength
     * 表名长度为0的情况，期望返回错误消息：Invalid table name: ''. 中包含的TableName与输入一致
     */
    public function testTableNameOfZeroLength() {
        $tablebody = array (
            "table_meta" => array (
                "table_name" => "",
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
        try {
            $this->otsClient->createTable ($tablebody);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * TableNameWithUnicode
     * 表名包含Unicode，期望返回错误信息：Invalid table name: '{TableName}'. 中包含的TableName与输入一致
     */
    public function testTableNameWithUnicode() {
        $tablebody = array (
            "table_meta" => array (
                "table_name" => "testU+0053",
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
        try {
            $this->otsClient->createTable ($tablebody);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid table name: '{$tablebody['table_meta']['table_name']}'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * 1KBTableName
     * 表名长度为1KB，期望返回错误信息：Invalid table name: '{TableName}'. 中包含的TableName与输入一致
     */
    public function testTableName1KB() {
        $name = "";
        for($i = 1; $i < 1025; $i ++) {
            $name .= "a";
        }
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $name,
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
        try {
            $this->otsClient->createTable ($tablebody);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid table name: '{$tablebody['table_meta']['table_name']}'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * NoPKInSchema
     * 测试CreateTable在TableMeta包含0个PK时的情况，期望返回错误消息：Failed to parse the ProtoBuf message
     */
    public function testNoPKInSchema() {
        global $usedTables;
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[1],
                "primary_key_schema" => array ()
            ),
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "The number of primary key columns must be in range: [1, 4]."; // TODO make right expect
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * OnePKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含1个PK时的情况
     */
    public function testOnePKInSchema() {
        global $usedTables;
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[2],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_STRING
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
                "PK1" => ColumnTypeConst::CONST_STRING
            )
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * FourPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含4个PK时的情况
     */
    public function testFourPKInSchema() {
        global $usedTables;
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[3],
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
        
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array (
                "PK1" => ColumnTypeConst::CONST_STRING,
                "PK2" => ColumnTypeConst::CONST_INTEGER,
                "PK3" => ColumnTypeConst::CONST_STRING,
                "PK4" => ColumnTypeConst::CONST_INTEGER
            )
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * TooMuchPKInSchema
     * 测试TableMeta包含1000个PK的情况，CreateTable期望返回错误消息：The number of primary key columns must be in range: [1, 4].
     */
    public function testTooMuchPKInSchema() {
        global $usedTables;
        $key = array ();
        for($i = 1; $i < 1001; $i ++) {
            $key['a' . $i] = ColumnTypeConst::CONST_INTEGER;
        }
        // print_r($key);die;
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[4],
                "primary_key_schema" => $key
            ),
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "The number of primary key columns must be in range: [1, 4].";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * IntegerPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 INTEGER 的情况。
     */
    public function testIntegerPKInSchema() {
        global $usedTables;
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[5],
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
                "table_name" => $usedTables[5],
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
     * 类型为 DOUBLE / BOOELAN / BINARY / INF_MIN / INF_MAX 的情况，期望返回错误
     */
    public function testInvalidPKInSchema() {
        global $usedTables;
        $tablebody1 = array (
            "table_meta" => array (
                "table_name" => $usedTables[4],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_DOUBLE,
                    "PK2" => ColumnTypeConst::CONST_DOUBLE
                )
            ),
            "reserved_throughput" => array (
                "capacity_unit" => array (
                    "read" => 0,
                    "write" => 0
                )
            )
        );
        $tablebody2 = array (
            "table_meta" => array (
                "table_name" => $usedTables[4],
                "primary_key_schema" => array (
                    "PK1" => ColumnTypeConst::CONST_BOOLEAN,
                    "PK2" => ColumnTypeConst::CONST_BOOLEAN
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
            $this->otsClient->createTable ($tablebody1);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "DOUBLE is an invalid type for the primary key.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
        try {
            $this->otsClient->createTable ($tablebody2);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSClientException $exc) {
            $c = "Column type must be one of 'INTEGER', 'STRING', 'BOOLEAN', 'DOUBLE', 'BINARY', 'INF_MIN', or 'INF_MAX'.";
            $this->assertEquals ($c, $exc->getMessage ());
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "BOOLEAN is an invalid type for the primary key.";
            $this->assertContains ($c, $exc->getMessage ());
        }
    }
    
    public function tearDown() {
        global $usedTables;
        $table_name = $usedTables;
        for($i = 0; $i < count ($table_name); $i ++) {
            $request = array (
                    "table_name" => $table_name[$i]
            );
            try {
                $this->otsClient->deleteTable ($request);
            } catch (\Aliyun\OTS\OTSServerException $exc) {
                if ($exc->getOTSErrorCode() == 'OTSObjectNotExist')
                    continue;
            }
        }
    }
}
