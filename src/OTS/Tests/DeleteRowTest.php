<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\ColumnTypeConst;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\RowExistenceExpectationConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
    "myTable"
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
            "read" => 0,
            "write" => 0
        )
    )
));
SDKTestBase::waitForTableReady ();
class DeleteRowTest extends SDKTestBase {
    
    /*
     *
     * TableNameOfZeroLength
     * 创建一个表，并删除，ListTable期望返回0个TableName。
     */
    public function testTableNameOfZeroLength() {
        global $usedTables;
        $deleterow = array (
            "table_name" => "",
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * 5ColumnInPK
     * 和表主键不一致，指定5个主键
     */
    public function testColumnInPK() {
        global $usedTables;
        $deleterow = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "aaa",
                "PK2" => "cc",
                "PK3" => "ccd",
                "PK4" => "cds",
                "PK5" => "11s"
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "The number of primary key columns must be in range: [1, 4].";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * ExpectExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_EXIST，期望服务端返回 Invalid Condition。
     */
    public function testExpectExistConditionWhenRowNotExist() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "att1" => "asds",
                "att2" => "sdsd"
            )
        );
        $this->otsClient->putRow ($tablename);
        $deleterow = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            "primary_key" => array (
                "PK1" => 2,
                "PK2" => "a2"
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Condition check failed.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * ExpectExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_EXIST，期望操作成功。
     */
    public function testExpectExistConditionWhenRowExist() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "att1" => "asds",
                "att2" => "sdsd"
            )
        );
        $this->otsClient->putRow ($tablename);
        $deleterow = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            )
        );
        $this->otsClient->deleteRow ($deleterow);
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "columns_to_get" => array ()
        );
        $getrow = $this->otsClient->getRow ($body);
        // print_r($getrow);die;
        $this->assertEmpty ($getrow['row']['primary_key_columns']);
        $this->assertEmpty ($getrow['row']['attribute_columns']);
    }
    
    /*
     *
     * ExpectNotExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_NOT_EXIST
     *
     */
    public function testExpectNotExistConditionWhenRowNotExist() {
        global $usedTables;
        $deleterow = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid condition: EXPECT_NOT_EXIST while deleting row.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    /*
     * ExpectNotExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_NOT_EXIST
     */
    public function testExpectNotExistConditionWhenRowExist() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "att1" => "asds",
                "att2" => "sdsd"
            )
        );
        $this->otsClient->putRow ($tablename);
        $deleterow = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            )
        );
        try {
            $this->otsClient->deleteRow ($deleterow);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid condition: EXPECT_NOT_EXIST while deleting row.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件下，删除数据行是否成功。
     */
    public function testDeleteRowWithColumnCondition() {
        global $usedTables;
        $put_query = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "att1" => "asds",
                "att2" => "sdsd"
            )
        );
        $this->otsClient->putRow ($put_query);
        
        $delete_query = array (
            "table_name" => $usedTables[0],
            "condition" => array (
                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                "column_filter" => array (
                    "column_name" => "attr1",
                    "value" => "asds",
                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                )
            ),
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            )
        );
        $this->otsClient->deleteRow ($delete_query);
        
        $get_query = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "columns_to_get" => array (
                "attr1",
                "attr2"
            )
        );
        $get_row_res = $this->otsClient->getRow ($get_query);
        $this->assertEquals (count ($get_row_res['row']['attribute_columns']), 0);
        
        $put_query2 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "att1" => "asds",
                "att2" => "sdsd"
            )
        );
        $this->otsClient->putRow ($put_query2);
        
        $delete_query2 = array (
            "table_name" => $usedTables[0],
            "condition" => array (
                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                "column_filter" => array (
                    "column_name" => "att1",
                    "value" => "asdsddd",
                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                )
            ),
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            )
        );
        try {
            $this->otsClient->deleteRow ($delete_query2);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $a = $exc->getMessage ();
            $c = "Condition check failed.";
            $this->assertContains ( $c, $a );
        }
    }
}

