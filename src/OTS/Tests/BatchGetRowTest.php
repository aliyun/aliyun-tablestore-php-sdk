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
    "myTable",
    "myTable1",
    "test8",
    "test9"
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
class BatchGetRowTest extends SDKTestBase {
    public function testmes() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "aa",
                "attr3" => "tas",
                "attr4" => 11
            )
        );
        $this->otsClient->putRow ($tablename);
    }
    
    /*
     *
     * EmptyBatchGetRow
     * BatchGetRow没有包含任何表的情况。
     */
    public function testEmptyBatchGetRow() {
        $batchGet = array ();
        try {
            $this->otsClient->batchGetRow ($batchGet);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No row specified in the request of BatchGetRow.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * EmptyBatchGetRow
     * BatchGetRow没有包含任何表的情况。
     */
    public function testEmpty1BatchGetRow() {
        global $usedTables;
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[2]
                ),
                array (
                    "table_name" => $usedTables[3]
                )
            )
        );
        // print_r();die;
        try {
            $this->otsClient->batchGetRow ($batchGet);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No row specified in table: '" . $usedTables[2] . "'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * 4ItemInBatchGetRow
     * BatchGetRow包含4个行。
     */
    public function testItemInBatchGetRow() {
        global $usedTables;
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    )
                )
            )
        );
        
        $getrow = $this->otsClient->batchGetRow ($batchGet);
        for($i = 0; $i < count ($batchGet['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($getrow['tables'][0]['rows'][$i]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][$i]['primary_key']);
        }
        // print_r($getrow);die;
    }
    
    /**
     * EmptyTableInBatchGetRow
     * BatchGetRow包含2个表，其中有1个表有1行，另外一个表为空的情况。抛出异常
     */
    public function testEmptyTableInBatchGetRow() {
        global $usedTables;
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1]
                )
            )
        );
        try {
            $this->otsClient->batchGetRow ($batchGet);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No row specified in table: '" . $usedTables[1] . "'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /**
     * 1000ItemInBatchGetRow
     * BatchGetRow包含1000个行，期望返回服务端错误？
     */
    public function testItemIn1000BatchGetRow() {
        global $usedTables;
        for($i = 0; $i < 200; $i ++) {
            $a[] = array (
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                )
            );
        }
        // print_r($a);die;
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => $a
                )
            )
        );
        try {
            $this->otsClient->batchGetRow ($batchGet);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Rows count exceeds the upper limit";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * OneTableOneFailInBatchGetRow
     * BatchGetRow有一个表中的一行失败的情况
     */
    public function testOneTableOneFailInBatchGetRow() {
        global $usedTables;
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK11" => 3,
                                "PK12" => "a3"
                            )
                        )
                    )
                )
            )
        );
        if (is_array ($this->otsClient->batchGetRow ($batchGet))) {
            $getrow = $this->otsClient->batchGetRow ($batchGet);
            // print_r($getrow);die;
            // print_r($getrow);die;
            $this->assertEquals ($getrow['tables'][0]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][1]['primary_key']);
            $this->assertEquals ($getrow['tables'][0]['rows'][2]['is_ok'], 0);
            $error = array (
                "code" => "OTSInvalidPK",
                "message" => "Primary key schema mismatch."
            );
            $this->assertEquals ($getrow['tables'][0]['rows'][2]['error'], $error);
            // $this->sssertEquals()
        }
    }
    
    /**
     * OneTableTwoFailInBatchGetRow
     * BatchGetRow有一个表中的一行失败的情况
     */
    public function testOneTableTwoFailInBatchGetRow() {
        global $usedTables;
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK11" => 2,
                                "PK22" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK11" => 3,
                                "PK12" => "a3"
                            )
                        )
                    )
                )
            )
        );
        if (is_array ($this->otsClient->batchGetRow ($batchGet))) {
            $getrow = $this->otsClient->batchGetRow ($batchGet);
            // print_r($getrow);die;
            // print_r($getrow);die;
            $this->assertEquals ($getrow['tables'][0]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['is_ok'], 0);
            $this->assertEquals ($getrow['tables'][0]['rows'][2]['is_ok'], 0);
            $error = array (
                "code" => "OTSInvalidPK",
                "message" => "Primary key schema mismatch."
            );
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['error'], $error);
            $this->assertEquals ($getrow['tables'][0]['rows'][2]['error'], $error);
            // $this->sssertEquals()
        }
    }
    
    /*
     *
     * TwoTableOneFailInBatchGetRow
     * BatchGetRow有2个表各有1行失败的情况
     */
    public function testTwoTableOneFailInBatchGetRow() {
        global $usedTables;
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $tablebody = array (
            "table_meta" => array (
                "table_name" => $usedTables[1],
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
        );
        $this->otsClient->createTable ($tablebody);
        $table = array (
            "table_name" => $usedTables[1],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => "a1"
            )
        );
        $this->waitForTableReady ();
        $this->otsClient->putRow ($table);
        $batchGet = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK11" => 2,
                                "PK22" => "a2"
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK11" => 2,
                                "PK22" => "a2"
                            )
                        )
                    )
                )
            )
        );
        if (is_array ($this->otsClient->batchGetRow ($batchGet))) {
            $error = array (
                "code" => "OTSInvalidPK",
                "message" => "Primary key schema mismatch."
            );
            $getrow = $this->otsClient->batchGetRow ($batchGet);
            // print_r($getrow);die;
            $this->assertEquals ($getrow['tables'][0]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['is_ok'], 0);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['error'], $error);
            $this->assertEquals ($getrow['tables'][1]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals ($getrow['tables'][1]['rows'][1]['is_ok'], 0);
            $this->assertEquals ($getrow['tables'][1]['rows'][1]['error'], $error);
        }
    }
    
    /**
     * 测试在单表中和单一ColumnCondition过滤条件下，使用BatchGetRow接口进行批量读取数据的操作是否成功。
     */
    public function testSingleTableBatchGetRowWithSingleCondition() {
        global $usedTables;
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $batchGetQuery = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "logical_operator" => LogicalOperatorConst::CONST_AND,
                        "sub_conditions" => array (
                            array (
                                "column_name" => "attr1",
                                "value" => 1,
                                "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                "column_name" => "attr2",
                                "value" => "a6",
                                "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                            )
                        )
                    )
                )
            )
        );
        $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGetQuery);
        
        $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['is_ok'], 1);
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['primary_key_columns']['PK1'], $i + 1);
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['primary_key_columns']['PK2'], "a" . ($i + 1));
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['attribute_columns']['attr1'], $i + 1);
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['attribute_columns']['attr2'], "a" . ($i + 1));
        }
        
        $batchGetQuery2 = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "column_name" => "attr1",
                        "value" => 100,
                        "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                    )
                )
            )
        );
        $batchGetQueryRes2 = $this->otsClient->batchGetRow ($batchGetQuery2);
        
        $this->assertEquals (count ($batchGetQueryRes2['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes2['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes2['tables'][0]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes2['tables'][0]['rows'][$i]['row']['attribute_columns']), 0);
        }
    }
    
    /**
     * 测试在单表中使用多重ColumnCondition过滤条件下，使用BatchGetRow接口进行批量数据读取的操作是否成功。
     */
    public function testSingleTableBatchGetRowWithMultipleCondition() {
        global $usedTables;
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $batchGetQuery = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (
                        "attr1",
                        "attr2"
                    ),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "logical_operator" => LogicalOperatorConst::CONST_AND,
                        "sub_conditions" => array (
                            array (
                                "column_name" => "attr1",
                                "value" => 1,
                                "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                "column_name" => "attr2",
                                "value" => "a6",
                                "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                            ),
                            array (
                                "logical_operator" => LogicalOperatorConst::CONST_OR,
                                "sub_conditions" => array (
                                    array (
                                        "column_name" => "attr1",
                                        "value" => 100,
                                        "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                    ),
                                    array (
                                        "column_name" => "attr2",
                                        "value" => "a0",
                                        "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGetQuery);
        
        $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['attribute_columns']), 0);
        }
    }
    
    /**
     * 测试在多表中和单一ColumnCondition过滤条件下，使用BatchGetRow接口进行批量数据读取操作是否成功。
     */
    public function testMultipleTablesBatchGetRowWithSingleCondition() {
        global $usedTables;
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $allTables = $this->otsClient->listTable (array ());
        if (! in_array ($usedTables[1], $allTables))
            $this->otsClient->createTable (array (
                "table_meta" => array (
                    "table_name" => $usedTables[1],
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
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                "table_name" => $usedTables[1],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        
        $batchGetQuery = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (
                        "attr1",
                        "attr2"
                    ),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "logical_operator" => LogicalOperatorConst::CONST_AND,
                        "sub_conditions" => array (
                            array (
                                "column_name" => "attr1",
                                "value" => 1,
                                "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                "column_name" => "attr2",
                                "value" => "a6",
                                "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                            ),
                            array (
                                "logical_operator" => LogicalOperatorConst::CONST_OR,
                                "sub_conditions" => array (
                                    array (
                                        "column_name" => "attr1",
                                        "value" => 100,
                                        "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                    ),
                                    array (
                                        "column_name" => "attr2",
                                        "value" => "a0",
                                        "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                    )
                                )
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1],
                    "columns_to_get" => array (
                        "attr1",
                        "attr2"
                    ),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "column_name" => "attr1",
                        "value" => 3,
                        "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                    )
                )
            )
        );
        $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['attribute_columns']), 0);
        }
        $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][1]['rows'][$i]['is_ok'], 1);
            if ($i < 2)
                $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows'][$i]['row']['attribute_columns']), 0);
            else {
                $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows'][$i]['row']['attribute_columns']), 2);
            }
        }
    }
    
    /**
     * 测试在多表中和多重ColumnCondition过滤条件下，使用BatchGetRow接口进行批量数据读取的操作是否成功。
     */
    public function testMultipleTablesBatchGetRowWithMultipleConditions() {
        global $usedTables;
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $allTables = $this->otsClient->listTable (array ());
        if (! in_array ($usedTables[1], $allTables))
            $this->otsClient->createTable (array (
                "table_meta" => array (
                    "table_name" => $usedTables[1],
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
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                "table_name" => $usedTables[1],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "attr1" => $i,
                    "attr2" => "a" . $i
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        
        $batchGetQuery = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "columns_to_get" => array (
                        "attr1",
                        "attr2"
                    ),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "logical_operator" => LogicalOperatorConst::CONST_AND,
                        "sub_conditions" => array (
                            array (
                                "column_name" => "attr1",
                                "value" => 1,
                                "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                "column_name" => "attr2",
                                "value" => "a6",
                                "comparator" => ComparatorTypeConst::CONST_LESS_THAN
                            ),
                            array (
                                "logical_operator" => LogicalOperatorConst::CONST_OR,
                                "sub_conditions" => array (
                                    array (
                                        "column_name" => "attr1",
                                        "value" => 100,
                                        "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                    ),
                                    array (
                                        "column_name" => "attr2",
                                        "value" => "a0",
                                        "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                    )
                                )
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1],
                    "columns_to_get" => array (
                        "attr1",
                        "attr2"
                    ),
                    "rows" => array (
                        array (
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    ),
                    "column_filter" => array (
                        "logical_operator" => LogicalOperatorConst::CONST_AND,
                        "sub_conditions" => array (
                            array (
                                "column_name" => "attr1",
                                "value" => 3,
                                "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                "logical_operator" => LogicalOperatorConst::CONST_NOT,
                                "sub_conditions" => array (
                                    array (
                                        "column_name" => "attr2",
                                        "value" => "a9",
                                        "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows'][$i]['row']['attribute_columns']), 0);
        }
        $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][1]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows'][$i]['row']['attribute_columns']), 0);
        }
    }
}

