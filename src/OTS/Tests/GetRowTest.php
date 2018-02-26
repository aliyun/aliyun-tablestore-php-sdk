<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\RowExistenceExpectationConst;
use Aliyun\OTS\LogicalOperatorConst;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\ColumnTypeConst;

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
));
SDKTestBase::waitForTableReady ();

SDKTestBase::putInitialData (array (
    "table_name" => $usedTables[0],
    "condition" => RowExistenceExpectationConst::CONST_IGNORE,
    "primary_key" => array (
        "PK1" => "a1",
        "PK2" => 1,
        "PK3" => "a11",
        "PK4" => 11
    ),
    "attribute_columns" => array (
        "attr1" => 1,
        "attr2" => "aa",
        "attr3" => "tas",
        "attr4" => 11
    )
));
class GetRowTest extends SDKTestBase {
    
    /*
     *
     * GetRowWithDefaultColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet参数为4个属性列，期望读出所有4个属性列。
     */
    public function testGetRowWith4AttributeColumnsToGet() {
        global $usedTables;
        
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "columns_to_get" => array (
                "attr1",
                "attr2",
                "attr3",
                "attr4"
            )
        );
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertEmpty ($getrow['row']['primary_key_columns']);
        $this->assertEquals ($getrow['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals ($getrow['row']['attribute_columns']['attr2'], "aa");
        $this->assertEquals ($getrow['row']['attribute_columns']['attr3'], "tas");
        $this->assertEquals ($getrow['row']['attribute_columns']['attr4'], 11);
    }
    
    /*
     *
     * GetRowWithDefaultColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求不设置ColumnsToGet，期望读出所有4个主键列和4个属性列。
     */
    public function testGetRowWithDefaultColumnsToGet() {
        global $usedTables;
        
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            )
        );
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertEquals ($getrow['row']['primary_key_columns']['PK1'], "a1");
        $this->assertEquals ($getrow['row']['primary_key_columns']['PK2'], 1);
        $this->assertEquals ($getrow['row']['primary_key_columns']['PK3'], "a11");
        $this->assertEquals ($getrow['row']['primary_key_columns']['PK4'], 11);
        $this->assertEquals ($getrow['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals ($getrow['row']['attribute_columns']['attr2'], "aa");
        $this->assertEquals ($getrow['row']['attribute_columns']['attr3'], "tas");
        $this->assertEquals ($getrow['row']['attribute_columns']['attr4'], 11);
    }
    
    /*
     * GetRowWith0ColumsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet为空数组，期望读出所有数据。
     */
    public function testGetRowWith0ColumsToGet() {
        global $usedTables;
        
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "columns_to_get" => array ()
        );
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertEquals ($getrow['row']['primary_key_columns'], $tablename['primary_key']);
        $this->assertEquals ($getrow['row']['attribute_columns'], $tablename['attribute_columns']);
    }
    
    /*
     * GetRowWith4ColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet包含其中2个主键列，2个属性列，期望返回数据包含参数中指定的列。
     */
    public function testGetRowWith4ColumnsToGet() {
        global $usedTables;
        
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "columns_to_get" => array (
                "PK1",
                "PK2",
                "attr1",
                "attr2"
            )
        );
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        $this->assertArrayHasKey ("PK1", $getrow['row']['primary_key_columns']);
        $this->assertArrayHasKey ("PK2", $getrow['row']['primary_key_columns']);
        $this->assertArrayHasKey ("attr1", $getrow['row']['attribute_columns']);
        $this->assertArrayHasKey ("attr2", $getrow['row']['attribute_columns']);
    }
    
    /*
     * GetRowWith1000ColumnsToGet
     * GetRow请求ColumnsToGet包含1000个不重复的列名，期望返回服务端错误
     */
    public function testGetRowWith1000ColumnsToGet() {
        global $usedTables;
        
        for($a = 1; $a < 1000; $a ++) {
            $b[] = 'a' . $a;
        }
        // echo $b;
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "columns_to_get" => $b
        );
        
        $this->otsClient->getRow ($body);
    }
    
    /*
     * GetRowWithDuplicateColumnsToGet
     * GetRow请求ColumnsToGet包含2个重复的列名,成功返回这一列的值
     */
    public function testGetRowWithDuplicateColumnsToGet() {
        global $usedTables;
        
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "columns_to_get" => array (
                "PK1",
                "PK1"
            )
        );
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $this->otsClient->putRow ($tablename);
        $getrow = $this->otsClient->getRow ($body);
        // if (is_array($getrow)) {
        // print_r($getrow);die;
        $this->assertEquals ($getrow['row']['primary_key_columns']["PK1"], $body['primary_key']['PK1']);
        // }
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件的情况下，获取数据行的操作是否成功。
     */
    public function testGetRowWithColumnFilterToGet() {
        global $usedTables;
        
        $putdata1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $putdata2 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a2",
                "PK2" => 2,
                "PK3" => "a22",
                "PK4" => 22
            ),
            "attribute_columns" => array (
                "attr1" => 2,
                "attr2" => "aaa",
                "attr3" => "tass",
                "attr4" => 22
            )
        );
        $this->otsClient->putRow ($putdata1);
        $this->otsClient->putRow ($putdata2);
        $querybody = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a2",
                "PK2" => 2,
                "PK3" => "a22",
                "PK4" => 22
            ),
            "columns_to_get" => array (
                "PK1",
                "PK2",
                "PK3",
                "PK4"
            ),
            "column_filter" => array (
                "logical_operator" => LogicalOperatorConst::CONST_AND,
                "sub_conditions" => array (
                    array (
                        "column_name" => "attr1",
                        "value" => 1,
                        "comparator" => ComparatorTypeConst::CONST_GREATER_THAN
                    ),
                    array (
                        "column_name" => "attr4",
                        "value" => 30,
                        "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                    )
                )
            )
        );
        $getrowres = $this->otsClient->getRow ($querybody);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK1"], $putdata2['primary_key']['PK1']);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK2"], $putdata2['primary_key']['PK2']);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK3"], $putdata2['primary_key']['PK3']);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK4"], $putdata2['primary_key']['PK4']);
    }
    
    /**
     * 查询在使用ColumnCondition的过滤条件的情况下，获取数据行的操作是否成功。
     */
    public function testGetRowWithColumnFilterToGet2() {
        global $usedTables;
        
        $putdata1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $putdata2 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "aa2",
                "PK2" => 2,
                "PK3" => "aa22",
                "PK4" => 22
            ),
            "attribute_columns" => array (
                "attr1" => 2,
                "attr2" => "aaa",
                "attr3" => "tass",
                "attr4" => 22
            )
        );
        $this->otsClient->putRow ($putdata1);
        $this->otsClient->putRow ($putdata2);
        $querybody = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "aa2",
                "PK2" => 2,
                "PK3" => "aa22",
                "PK4" => 22
            ),
            "columns_to_get" => array (
                "attr1",
                "attr2",
                "attr3",
                "attr4"
            ),
            "column_filter" => array (
                "logical_operator" => LogicalOperatorConst::CONST_NOT,
                "sub_conditions" => array (
                    array (
                        "column_name" => "attr4",
                        "value" => 22,
                        "comparator" => ComparatorTypeConst::CONST_NOT_EQUAL
                    )
                )
            )
        );
        $getrowres = $this->otsClient->getRow ($querybody);
        
        $this->assertEquals ($getrowres['row']['attribute_columns']["attr1"], $putdata2['attribute_columns']['attr1']);
        $this->assertEquals ($getrowres['row']['attribute_columns']["attr2"], $putdata2['attribute_columns']['attr2']);
        $this->assertEquals ($getrowres['row']['attribute_columns']["attr3"], $putdata2['attribute_columns']['attr3']);
        $this->assertEquals ($getrowres['row']['attribute_columns']["attr4"], $putdata2['attribute_columns']['attr4']);
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件和过滤的某项数据列缺失的情况下，获取查询数据航是否成功。
     */
    public function testGetRowWithColumnFilterAndMissingField() {
        global $usedTables;
        
        $putdata1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $putdata2 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a2",
                "PK2" => 2,
                "PK3" => "a22",
                "PK4" => 22
            ),
            "attribute_columns" => array (
                "attr1" => 2,
                "attr2" => "aaa",
                "attr3" => "tass",
                "attr4" => 22
            )
        );
        $this->otsClient->putRow ($putdata1);
        $this->otsClient->putRow ($putdata2);
        $querybody = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a2",
                "PK2" => 2,
                "PK3" => "a22",
                "PK4" => 22
            ),
            "columns_to_get" => array (
                "PK1",
                "PK2",
                "PK3",
                "PK4"
            ),
            "column_filter" => array (
                "logical_operator" => LogicalOperatorConst::CONST_AND,
                "sub_conditions" => array (
                    array (
                        "column_name" => "attr55",
                        "value" => 1,
                        "comparator" => ComparatorTypeConst::CONST_GREATER_THAN,
                        "pass_if_missing" => false
                    ),
                    array (
                        "column_name" => "attr4",
                        "value" => 30,
                        "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                    )
                )
            )
        );
        $getrowres = $this->otsClient->getRow ($querybody);
        $this->assertEquals (count ($getrowres['row']['primary_key_columns']), 0);
        $this->assertEquals (count ($getrowres['row']['attribute_columns']), 0);
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件和多重逻辑运算符的情况下，获取查询的数据行是否成功。
     */
    public function testGetRowWithColumnFilterAndMultipleLogicalOperatorsToGet() {
        global $usedTables;
        
        $putdata1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a1",
                "PK2" => 1,
                "PK3" => "a11",
                "PK4" => 11
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $putdata2 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => "a2",
                "PK2" => 2,
                "PK3" => "a22",
                "PK4" => 22
            ),
            "attribute_columns" => array (
                "attr1" => 2,
                "attr2" => "aaa",
                "attr3" => "tass",
                "attr4" => 22
            )
        );
        $this->otsClient->putRow ($putdata1);
        $this->otsClient->putRow ($putdata2);
        $querybody = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => "a2",
                "PK2" => 2,
                "PK3" => "a22",
                "PK4" => 22
            ),
            "columns_to_get" => array (
                "PK1",
                "PK2",
                "PK3",
                "PK4"
            ),
            "column_filter" => array (
                "logical_operator" => LogicalOperatorConst::CONST_AND,
                "sub_conditions" => array (
                    array (
                        "column_name" => "attr1",
                        "value" => 1,
                        "comparator" => ComparatorTypeConst::CONST_GREATER_THAN
                    ),
                    array (
                        "column_name" => "attr4",
                        "value" => 30,
                        "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                    ),
                    array (
                        "logical_operator" => LogicalOperatorConst::CONST_OR,
                        "sub_conditions" => array (
                            array (
                                "column_name" => "attr2",
                                "value" => "aaaaa",
                                "comparator" => ComparatorTypeConst::CONST_EQUAL
                            ),
                            array (
                                "column_name" => "attr3",
                                "value" => "tass",
                                "comparator" => ComparatorTypeConst::CONST_EQUAL
                            )
                        )
                    )
                )
            )
        );
        $getrowres = $this->otsClient->getRow ($querybody);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK1"], $putdata2['primary_key']['PK1']);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK2"], $putdata2['primary_key']['PK2']);
        $this->assertEquals ($getrowres['row']['primary_key_columns']["PK3"], $putdata2['primary_key']['PK3']);
        $this->assertEquals ($getrowres['row']['primary_key_columns'] ["PK4"], $putdata2 ['primary_key'] ['PK4'] );
    }
}

