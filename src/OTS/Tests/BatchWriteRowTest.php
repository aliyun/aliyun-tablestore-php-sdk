<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\RowExistenceExpectationConst;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\LogicalOperatorConst;
use Aliyun\OTS\ColumnTypeConst;
use Aliyun\OTS\DirectionConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
    "myTable",
    "myTable1",
    "test1",
    "test2",
    "test3",
    "test4"
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
class BatchWriteRowTest extends SDKTestBase {
    
    /*
     *
     * GetEmptyBatchWriteRow
     * BatchWriteRow没有包含任何表的情况
     */
    public function testGetEmptyBatchWriteRow() {
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => 'test9'
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No row specified in table: 'test9'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * EmptyTableInBatchWriteRow
     * BatchWriteRow包含2个表，其中有1个表有1行，另外一个表为空的情况。
     */
    public function testGetRowWith0ColumsToGet() {
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => 'test9',
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    )
                ),
                array (
                    "table_name" => 'test8'
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No row specified in table: 'test8'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * PutOnlyInBatchWriteRow
     * BatchWriteRow包含4个Put操作
     */
    public function testPutOnlyInBatchWriteRow() {
        global $usedTables;
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name1",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name2",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name3",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name4",
                                "att2" => 256
                            )
                        )
                    )
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        for($i = 1; $i < 5; $i ++) {
            $body = array (
                "table_name" => $usedTables[0],
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "columns_to_get" => array ()
            );
            $a[] = $this->otsClient->getRow ($body);
        }
        $this->assertEquals (count ($a), 4);
        for($c = 0; $c < count ($a); $c ++) {
            $this->assertEquals ($a[$c]['row']['primary_key_columns'], $batchWrite['tables'][0]['put_rows'][$c]['primary_key']);
            $this->assertEquals ($a[$c]['row']['attribute_columns'], $batchWrite['tables'][0]['put_rows'][$c]['attribute_columns']);
        }
    }
    
    /*
     * UpdateOnlyInBatchWriteRow
     * BatchWriteRow包含4个Update操作
     */
    public function testUpdateOnlyInBatchWriteRow() {
        global $usedTables;
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name1",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name2",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name3",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name4",
                                "att2" => 256
                            )
                        )
                    )
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        )
                    )
                )
            )
        )
        // //////添加多行插入 put_rows
        
        ;
        $this->otsClient->batchWriteRow ($batchWrite1);
        for($i = 1; $i < 5; $i ++) {
            $body = array (
                "table_name" => $usedTables[0],
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "columns_to_get" => array ()
            );
            $a[] = $this->otsClient->getRow ($body);
        }
        $this->assertEquals (count ($a), 4);
        for($c = 0; $c < count ($a); $c ++) {
            // print_r($a[$c]['row']['primary_key_columns']);
            // print_r($batchWrite1['tables'][0]['update_rows'][0]['attribute_columns_to_put']);
            $this->assertEquals ($a[$c]['row']['primary_key_columns'], $batchWrite['tables'][0]['put_rows'][$c]['primary_key']);
            $this->assertEquals ($a[$c]['row']['attribute_columns'], $batchWrite1['tables'][0]['update_rows'][$c]['attribute_columns_to_put']);
        }
    }
    
    /*
     * DeleteOnlyInBatchWriteRow
     * BatchWriteRow包含4个Delete操作
     */
    public function testDeleteOnlyInBatchWriteRow() {
        global $usedTables;
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name1",
                                "att2" => -256.66
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name2",
                                "att2" => -256.66
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name3",
                                "att2" => -256.66
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name4",
                                "att2" => -256.66
                            )
                        )
                    )
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    )
                )
            )
        )
        // //////添加多行插入 put_rows
        
        ;
        $getrow = $this->otsClient->batchWriteRow ($batchWrite1);
        
        for($i = 1; $i < 5; $i ++) {
            $body = array (
                "table_name" => $usedTables[0],
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "columns_to_get" => array ('att2')
            );
            $a[] = $this->otsClient->getRow ($body);
        }
        $this->assertEquals (count ($a), 4);
        
        for($c = 0; $c < count ($a); $c ++) {
            $this->assertEmpty ($a[$c]['row']['primary_key_columns']);
            $this->assertEmpty ($a[$c]['row']['attribute_columns']);
        }
    }
    
    /*
     * 4PutUpdateDeleteInBatchWriteRow
     * BatchWriteRow同时包含4个Put，4个Update和4个Delete操作
     */
    public function testPutUpdateDeleteInBatchWriteRow() {
        global $usedTables;
        for($i = 1; $i < 9; $i ++) {
            $put[] = array (
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "att1" => "name{$i}",
                    "att2" => 256
                )
            );
        }
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => $put
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 9,
                                "PK2" => "a9"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 12,
                                "PK2" => "a12"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 5,
                                "PK2" => "a5"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 6,
                                "PK2" => "a6"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 7,
                                "PK2" => "a7"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 8,
                                "PK2" => "a8"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    )
                )
            )
        );
        $getrow = $this->otsClient->batchWriteRow ($batchWrite1);
        $getRange = array (
            "table_name" => $usedTables[0],
            "direction" => DirectionConst::CONST_FORWARD,
            "columns_to_get" => array (),
            "limit" => 100,
            "inclusive_start_primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "exclusive_end_primary_key" => array (
                "PK1" => 30,
                "PK2" => "a30"
            )
        );
        $a = $this->otsClient->getRange ($getRange);
        $this->assertEquals (count ($a['rows']), 8);
        for($i = 0; $i < count ($a['rows']); $i ++) {
            $row = $a['rows'][$i];
            $pk1 = $row['primary_key_columns']['PK1'];
            $columns = $row['attribute_columns'];
            $this->assertEquals ($pk1, $i + 5);
            // 1-4 rows deleted
            if ($pk1 >= 5 && $pk1 <= 8) {
                // 5-8 rows updated
                $this->assertEquals ($columns['att1'], 'Zhon');
            } elseif ($pk1 >= 9 && $pk1 <= 12) {
                // 9-12 rows put
                $this->assertEquals ($columns['att1'], 'name');
                $this->assertEquals ($columns['att2'], 256);
            } else {
                $this->fail ("Deleted rows read.");
            }
        }
    }
    
    /*
     * 1000PutUpdateDeleteInBatchWriteRow
     * BatchWriteRow同时包含1000个Put，4个Update和4个Delete操作，期望返回服务端错误
     */
    public function testPut1000UpdateDeleteInBatchWriteRow() {
        global $usedTables;
        for($i = 1; $i < 1000; $i ++) {
            $a[] = array (
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => array (
                    "PK1" => $i,
                    "PK2" => "a" . $i
                ),
                "attribute_columns" => array (
                    "att1" => "name",
                    "att2" => 256
                )
            );
        }
        // print_r($a);die;
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => $a,
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 5,
                                "PK2" => "a5"
                            ),
                            "attribute_columns" => array (
                                array (
                                    "att1" => 'Zhon',
                                    "type" => "PUT"
                                ),
                                array (
                                    "att2" => 256,
                                    "type" => "DELETE"
                                )
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 6,
                                "PK2" => "a6"
                            ),
                            "attribute_columns" => array (
                                array (
                                    "att1" => 'Zhon',
                                    "type" => "PUT"
                                ),
                                array (
                                    "att2" => 256,
                                    "type" => "DELETE"
                                )
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 7,
                                "PK2" => "a7"
                            ),
                            "attribute_columns" => array (
                                array (
                                    "att1" => 'Zhon',
                                    "type" => "PUT"
                                ),
                                array (
                                    "att2" => 256,
                                    "type" => "DELETE"
                                )
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 8,
                                "PK2" => "a8"
                            ),
                            "attribute_columns" => array (
                                array (
                                    "att1" => 'Zhon',
                                    "type" => "PUT"
                                ),
                                array (
                                    "att2" => 256,
                                    "type" => "DELETE"
                                )
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 2,
                                "PK2" => "a2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 3,
                                "PK2" => "a3"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 4,
                                "PK2" => "a4"
                            )
                        )
                    )
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Rows count exceeds the upper limit";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * 4TablesInBatchWriteRow
     * BatchWriteRow包含4个表的情况。
     */
    public function testTables4InBatchWriteRow() {
        for($i = 1; $i < 5; $i ++) {
            $tablebody = array (
                "table_meta" => array (
                    "table_name" => "test" . $i,
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
        }
        $this->waitForTableReady ();
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => 'test1',
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    )
                ),
                array (
                    "table_name" => 'test2',
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    )
                ),
                array (
                    "table_name" => 'test3',
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    )
                ),
                array (
                    "table_name" => 'test4',
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 1,
                                "PK2" => "a1"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWrite);
        for($i = 1; $i < 5; $i ++) {
            $body = array (
                "table_name" => "test" . $i,
                "primary_key" => array (
                    "PK1" => 1,
                    "PK2" => "a1"
                ),
                "columns_to_get" => array ()
            );
            $getrow[] = $this->otsClient->getRow ($body);
        }
        $primary = array (
            "PK1" => 1,
            "PK2" => "a1"
        );
        $columns = array (
            "att1" => "name",
            "att2" => 256
        );
        $this->assertEquals (count ($getrow), 4);
        for($i = 0; $i < count ($getrow); $i ++) {
            $this->assertEquals ($getrow[$i]['row']['primary_key_columns'], $primary);
            $this->assertEquals ($getrow[$i]['row']['attribute_columns'], $columns);
        }
    }
    
    /*
     * OneTableOneFailInBatchWriteRow
     * BatchWriteRow有一个表中的一行失败的情况
     */
    public function testOneTableOneFailInBatchWriteRow() {
        global $usedTables;
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 9,
                                "PK2" => "a9"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            "primary_key" => array (
                                "PK1" => 510,
                                "PK2" => "a510"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 6,
                                "PK2" => "a6"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 12,
                                "PK2" => "a12"
                            )
                        )
                    )
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow ($batchWrite);
        $this->assertEquals ($writerow['tables'][0]['update_rows'][0]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['update_rows'][0]['error'], array (
            "code" => "OTSConditionCheckFail",
            "message" => "Condition check failed."
        ));
    }
    
    /*
     * OneTableTwoFailInBatchWriteRow
     * BatchWriteRow有一个表中的二行失败的情况
     */
    public function testOneTableTwoFailInBatchWriteRow() {
        global $usedTables;
        $pkOfRows = array (
            array (
                "PK1" => 9,
                "PK2" => "a9"
            ),
            array (
                "PK1" => 10,
                "PK2" => "a10"
            ),
            array (
                "PK1" => 510,
                "PK2" => "a510"
            ),
            array (
                "PK1" => 6,
                "PK2" => "a6"
            ),
            array (
                "PK1" => 11,
                "PK2" => "a11"
            ),
            array (
                "PK1" => 12,
                "PK2" => "a12"
            )
        );
        
        foreach ($pkOfRows as $pk) {
            $this->otsClient->deleteRow (array (
                "table_name" => $usedTables[0],
                "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                "primary_key" => $pk
            ));
        }
        
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 9,
                                "PK2" => "a9"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            "primary_key" => array (
                                "PK1" => 510,
                                "PK2" => "a510"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            "primary_key" => array (
                                "PK1" => 6,
                                "PK2" => "a6"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        ),
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 12,
                                "PK2" => "a12"
                            )
                        )
                    )
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow ($batchWrite);
        $this->assertEquals ($writerow['tables'][0]['update_rows'][0]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['update_rows'][1]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['update_rows'][0]['error'], array (
            "code" => "OTSConditionCheckFail",
            "message" => "Condition check failed."
        ));
        $this->assertEquals ($writerow['tables'][0]['update_rows'][1]['error'], array (
            "code" => "OTSConditionCheckFail",
            "message" => "Condition check failed."
        ));
    }
    
    /*
     * TwoTableOneFailInBatchWriteRow
     * BatchWriteRow有2个表各有1行失败的情况
     */
    public function testTwoTableOneFailInBatchWriteRow() {
        global $usedTables;
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
        $this->waitForTableReady ();
        $batchWrite = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 9,
                                "PK2" => "a9"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            "primary_key" => array (
                                "PK1" => 510,
                                "PK2" => "a510"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1],
                    "put_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 9,
                                "PK2" => "a9"
                            ),
                            "attribute_columns" => array (
                                "att1" => "name",
                                "att2" => 256
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            "primary_key" => array (
                                "PK1" => 510,
                                "PK2" => "a510"
                            ),
                            "attribute_columns_to_put" => array (
                                "att1" => 'Zhon'
                            ),
                            "attribute_columns_to_delete" => array (
                                "att2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow ($batchWrite);
        // print_r($writerow);die;
        $this->assertEquals ($writerow['tables'][0]['update_rows'][0]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][1]['update_rows'][0]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['update_rows'][0]['error'], array (
            "code" => "OTSConditionCheckFail",
            "message" => "Condition check failed."
        ));
        $this->assertEquals ($writerow['tables'][1]['update_rows'][0]['error'], array (
            "code" => "OTSConditionCheckFail",
            "message" => "Condition check failed."
        ));
    }
    
    /*
     * 1000TablesInBatchWriteRow
     * BatchWriteRow包含1000个表的情况，期望返回服务端错误
     */
    public function testP1000TablesInBatchWriteRow() {
        for($i = 1; $i < 1001; $i ++) {
            $res[] = array (
                "table_name" => 'test' . $i,
                "put_rows" => array (
                    array (
                        "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                        "primary_key" => array (
                            "PK1" => 1,
                            "PK2" => "a1"
                        ),
                        "attribute_columns" => array (
                            "att1" => "name",
                            "att2" => 256
                        )
                    )
                )
            );
        }
        $batchWrite = array (
            "tables" => $res
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Rows count exceeds the upper limit";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /**
     * 测试在单表上使用单一ColumnCondition的过滤条件下，使用BatchWriteRow接口进行批量写入操作是否成功。
     */
    public function testBatchWriteRowWithSingleColumnConditionAndSingleTable() {
        // To prepare the environment.
        global $usedTables;
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (! in_array ($usedTables[$i], $tables)) {
                $tablemeta = array (
                    "table_meta" => array (
                        "table_name" => $usedTables[$i],
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
                $this->otsClient->createTable ($tablemeta);
                $this->waitForTableReady ();
            }
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    "table_name" => $usedTables[$i],
                    "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                    "primary_key" => array (
                        "PK1" => $k,
                        "PK2" => "a" . $k
                    ),
                    "attribute_columns" => array (
                        "attr1" => $k,
                        "attr2" => "aa",
                        "attr3" => "tas",
                        "attr4" => $k . "-" . $k
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        $this->waitForTableReady ();
        // begin testing
        $batchWriteData = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "column_name" => "attr1",
                                    "value" => 19,
                                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 19,
                                "PK2" => "a19"
                            ),
                            "attribute_columns" => array (
                                "attr1" => 109,
                                "attr2" => "aa109"
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                "column_filter" => array (
                                    "column_name" => "attr1",
                                    "value" => 99,
                                    "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            ),
                            "attribute_columns_to_put" => array (
                                "attr1" => 990
                            ),
                            "attribute_columns_to_delete" => array (
                                "attr2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "column_name" => "attr2",
                                    "value" => "ab",
                                    "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
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
                                "PK1" => 19,
                                "PK2" => "a19"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr1'], 109);
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr2'], "aa109");
        
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr1'], 990);
        $this->assertFalse (isset ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr2']));
        
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows'][2]['row']['attribute_columns']), 0);
    }
    
    /**
     * 测试在单表中使用多重ColumnCondition的过滤条件下，使用BatchWriteRow接口进行批量写入操作是否成功。
     */
    public function testBatchWriteRowWithMultipleColumnConditionsAndSingleTables() {
        // To prepare the environment.
        global $usedTables;
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (in_array ($usedTables[$i], $tables)) {
                $this->otsClient->deleteTable (array (
                    "table_name" => $usedTables[$i]
                ));
            }
            $tablemeta = array (
                "table_meta" => array (
                    "table_name" => $usedTables[$i],
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
            $this->otsClient->createTable ($tablemeta);
            $this->waitForTableReady ();
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    "table_name" => $usedTables[$i],
                    "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                    "primary_key" => array (
                        "PK1" => $k,
                        "PK2" => "a" . $k
                    ),
                    "attribute_columns" => array (
                        "attr1" => $k,
                        "attr2" => "aa",
                        "attr3" => "tas",
                        "attr4" => $k . "-" . $k
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        // begin testing
        $batchWriteData = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_NOT,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 19,
                                            "comparator" => ComparatorTypeConst::CONST_NOT_EQUAL
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 19,
                                "PK2" => "a19"
                            ),
                            "attribute_columns" => array (
                                "attr1" => 109,
                                "attr2" => "aa109"
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_AND,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 99,
                                            "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                        ),
                                        array (
                                            "logical_operator" => LogicalOperatorConst::CONST_OR,
                                            "sub_conditions" => array (
                                                array (
                                                    "column_name" => "attr2",
                                                    "value" => "aa",
                                                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                                                ),
                                                array (
                                                    "column_name" => "attr2",
                                                    "value" => "ddddd",
                                                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            ),
                            "attribute_columns_to_put" => array (
                                "attr1" => 990
                            ),
                            "attribute_columns_to_delete" => array (
                                "attr2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "column_name" => "attr2",
                                    "value" => "ab",
                                    "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        
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
                                "PK1" => 19,
                                "PK2" => "a19"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr1'], 109);
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr2'], "aa109");
        
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr1'], 990);
        $this->assertFalse (isset ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr2']));
        
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows'][2]['row']['attribute_columns']), 0);
    }
    
    /**
     * 测试在多表中和单一ColumnCondition的过滤条件下，使用BatchWriteRow接口进行批量写入的操作是否成功。
     */
    public function testBatchWriteRowWithSingleColumnConditionAndMultipleTables() {
        // To prepare the environment.
        global $usedTables;
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (! in_array ($usedTables[$i], $tables)) {
                $tablemeta = array (
                    "table_meta" => array (
                        "table_name" => $usedTables[$i],
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
                $this->otsClient->createTable ($tablemeta);
                $this->waitForTableReady ();
            }
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    "table_name" => $usedTables[$i],
                    "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                    "primary_key" => array (
                        "PK1" => $k,
                        "PK2" => "a" . $k
                    ),
                    "attribute_columns" => array (
                        "attr1" => $k,
                        "attr2" => "aa",
                        "attr3" => "tas",
                        "attr4" => $k . "-" . $k
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        // begin testing
        $batchWriteData = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "column_name" => "attr1",
                                    "value" => 19,
                                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 19,
                                "PK2" => "a19"
                            ),
                            "attribute_columns" => array (
                                "attr1" => 109,
                                "attr2" => "aa109"
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                "column_filter" => array (
                                    "column_name" => "attr1",
                                    "value" => 99,
                                    "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            ),
                            "attribute_columns_to_put" => array (
                                "attr1" => 990
                            ),
                            "attribute_columns_to_delete" => array (
                                "attr2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "column_name" => "attr2",
                                    "value" => "ab",
                                    "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1],
                    "put_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE
                            ),
                            "primary_key" => array (
                                "PK1" => 119,
                                "PK2" => "a119"
                            ),
                            "attribute_columns" => array (
                                "attr1" => 119,
                                "attr2" => "aa119"
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_AND,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 10,
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            "column_name" => "attr2",
                                            "value" => "aa",
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            ),
                            "attribute_columns_to_put" => array (
                                "attr1" => 1000
                            ),
                            "attribute_columns_to_delete" => array (
                                "attr2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_OR,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 11,
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            "column_name" => "attr2",
                                            "value" => "aabbb",
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        
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
                                "PK1" => 19,
                                "PK2" => "a19"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
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
                                "PK1" => 119,
                                "PK2" => "a119"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        
        // to verify the first updated table
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr1'], 109);
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr2'], "aa109");
        
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr1'], 990);
        $this->assertFalse (isset ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr2']));
        
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows'][2]['row']['attribute_columns']), 0);
        
        // to verify the second updated table
        $this->assertEquals (count ($batchGetRes['tables'][1]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][1]['rows'][$i]['is_ok'], 1);
        }
        $this->assertEquals ($batchGetRes['tables'][1]['rows'][0]['row']['attribute_columns']['attr1'], 119);
        $this->assertEquals ($batchGetRes['tables'][1]['rows'][0]['row']['attribute_columns']['attr2'], "aa119");
        
        $this->assertEquals ($batchGetRes['tables'][1]['rows'][1]['row']['attribute_columns']['attr1'], 1000);
        $this->assertFalse (isset ($batchGetRes['tables'][1]['rows'][1]['row']['attribute_columns']['attr2']));
        
        $this->assertEquals (count ($batchGetRes['tables'][1]['rows'][2]['row']['attribute_columns']), 0);
    }
    
    /**
     * 测试在多表中使用多重ColumnCondition过滤条件下，使用BatchWriteRow接口进行批量写入是否成功。
     */
    public function testBatchWriteRowWithMultipleColumnConditionsAndMultipleTables() {
        // To prepare the environment.
        global $usedTables;
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (in_array ($usedTables[$i], $tables)) {
                $this->otsClient->deleteTable (array (
                    "table_name" => $usedTables[$i]
                ));
            }
            $tablemeta = array (
                "table_meta" => array (
                    "table_name" => $usedTables[$i],
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
            $this->otsClient->createTable ($tablemeta);
            $this->waitForTableReady ();
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    "table_name" => $usedTables[$i],
                    "condition" => RowExistenceExpectationConst::CONST_IGNORE,
                    "primary_key" => array (
                        "PK1" => $k,
                        "PK2" => "a" . $k
                    ),
                    "attribute_columns" => array (
                        "attr1" => $k,
                        "attr2" => "aa",
                        "attr3" => "tas",
                        "attr4" => $k . "-" . $k
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        // begin testing
        $batchWriteData = array (
            "tables" => array (
                array (
                    "table_name" => $usedTables[0],
                    "put_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_NOT,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 19,
                                            "comparator" => ComparatorTypeConst::CONST_NOT_EQUAL
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 19,
                                "PK2" => "a19"
                            ),
                            "attribute_columns" => array (
                                "attr1" => 109,
                                "attr2" => "aa109"
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_AND,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 99,
                                            "comparator" => ComparatorTypeConst::CONST_GREATER_EQUAL
                                        ),
                                        array (
                                            "logical_operator" => LogicalOperatorConst::CONST_OR,
                                            "sub_conditions" => array (
                                                array (
                                                    "column_name" => "attr2",
                                                    "value" => "aa",
                                                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                                                ),
                                                array (
                                                    "column_name" => "attr2",
                                                    "value" => "ddddd",
                                                    "comparator" => ComparatorTypeConst::CONST_EQUAL
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            ),
                            "attribute_columns_to_put" => array (
                                "attr1" => 990
                            ),
                            "attribute_columns_to_delete" => array (
                                "attr2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "column_name" => "attr2",
                                    "value" => "ab",
                                    "comparator" => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                ),
                array (
                    "table_name" => $usedTables[1],
                    "put_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE
                            ),
                            "primary_key" => array (
                                "PK1" => 119,
                                "PK2" => "a119"
                            ),
                            "attribute_columns" => array (
                                "attr1" => 119,
                                "attr2" => "aa119"
                            )
                        )
                    ),
                    // //////添加多行插入 put_rows
                    "update_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_AND,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 10,
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            "column_name" => "attr2",
                                            "value" => "aa",
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            ),
                            "attribute_columns_to_put" => array (
                                "attr1" => 1000
                            ),
                            "attribute_columns_to_delete" => array (
                                "attr2"
                            )
                        )
                    ),
                    "delete_rows" => array (
                        array (
                            "condition" => array (
                                "row_existence" => RowExistenceExpectationConst::CONST_IGNORE,
                                "column_filter" => array (
                                    "logical_operator" => LogicalOperatorConst::CONST_OR,
                                    "sub_conditions" => array (
                                        array (
                                            "column_name" => "attr1",
                                            "value" => 11,
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            "column_name" => "attr2",
                                            "value" => "aabbb",
                                            "comparator" => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        
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
                                "PK1" => 19,
                                "PK2" => "a19"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 99,
                                "PK2" => "a99"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
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
                                "PK1" => 119,
                                "PK2" => "a119"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 10,
                                "PK2" => "a10"
                            )
                        ),
                        array (
                            "primary_key" => array (
                                "PK1" => 11,
                                "PK2" => "a11"
                            )
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        
        // to verify the first updated table
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr1'], 109);
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][0]['row']['attribute_columns']['attr2'], "aa109");
        
        $this->assertEquals ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr1'], 990);
        $this->assertFalse (isset ($batchGetRes['tables'][0]['rows'][1]['row']['attribute_columns']['attr2']));
        
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows'][2]['row']['attribute_columns']), 0);
        
        // to verify the second updated table
        $this->assertEquals (count ($batchGetRes['tables'][1]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][1]['rows'][$i]['is_ok'], 1);
        }
        $this->assertEquals ($batchGetRes['tables'][1]['rows'][0]['row']['attribute_columns']['attr1'], 119);
        $this->assertEquals ($batchGetRes['tables'][1]['rows'][0]['row']['attribute_columns']['attr2'], "aa119");
        
        $this->assertEquals ($batchGetRes['tables'][1]['rows'][1]['row']['attribute_columns']['attr1'], 1000);
        $this->assertFalse (isset ($batchGetRes['tables'][1]['rows'][1]['row']['attribute_columns']['attr2']));
        
        $this->assertEquals ( count ( $batchGetRes ['tables'] [1] ['rows'] [2] ['row'] ['attribute_columns'] ), 0 );
    }
}

