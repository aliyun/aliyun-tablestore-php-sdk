<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\OperationTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\LogicalOperatorConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\DirectionConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';




class BatchWriteRowTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable',
        'myTable1',
        'test1',
        'test2',
        'test3',
        'test4'
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
                    'read' => 0,
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
            )
        ));
        SDKTestBase::waitForTableReady ();
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp ( self::$usedTables );
    }
    
    /*
     *
     * GetEmptyBatchWriteRow
     * BatchWriteRow没有包含任何表的情况
     */
    public function testGetEmptyBatchWriteRow() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => 'test9'
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No operation is specified for table: 'test9'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * EmptyTableInBatchWriteRow
     * BatchWriteRow包含2个表，其中有1个表有1行，另外一个表为空的情况。
     */
    public function testGetRowWith0ColumsToGet() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => 'test9',
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        )
                    )
                ),
                array (
                    'table_name' => 'test8'
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "No operation is specified for table: 'test8'.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * PutOnlyInBatchWriteRow
     * BatchWriteRow包含4个Put操作
     */
    public function testPutOnlyInBatchWriteRow() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name1'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name2'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name3'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name4'),
                                array('att2', 256)
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
                'table_name' => self::$usedTables[0],
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'max_versions' => 1,
                'columns_to_get' => array ()
            );
            $a[] = $this->otsClient->getRow ($body);
        }
        $this->assertEquals (count ($a), 4);
        for($c = 0; $c < count ($a); $c ++) {
            $this->assertEquals ($a[$c]['primary_key'], $batchWrite['tables'][0]['rows'][$c]['primary_key']);
            $this->assertColumnEquals($batchWrite['tables'][0]['rows'][$c]['attribute_columns'], $a[$c]['attribute_columns'] );
        }
    }
    
    /*
     * UpdateOnlyInBatchWriteRow
     * BatchWriteRow包含4个Update操作
     */
    public function testUpdateOnlyInBatchWriteRow() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name1'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name2'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name3'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name4'),
                                array('att2', 256)
                            )
                        )
                    )
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
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
                'table_name' => self::$usedTables[0],
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'max_versions' => 1,
                'columns_to_get' => array ()
            );
            $a[] = $this->otsClient->getRow ($body);
        }
        $this->assertEquals (count ($a), 4);
        for($c = 0; $c < count ($a); $c ++) {
            // print_r($a[$c]['primary_key']);
            // print_r($batchWrite1['tables'][0]['update_rows'][0]['attribute_columns_to_put']);
            $this->assertEquals ($a[$c]['primary_key'], $batchWrite['tables'][0]['rows'][$c]['primary_key']);
            $this->assertColumnEquals($batchWrite1['tables'][0]['rows'][$c]['update_of_attribute_columns']['PUT'], $a[$c]['attribute_columns']);
        }
    }

    /*
     * UpdateOnlyWithIncrementInBatchWriteRow
     * BatchWriteRow包含Increment\Put操作
     */
    public function testUpdateOnlyWithIncrementInBatchWriteRow() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'inc')
                            ),
                            'attribute_columns' => array (
                                array('inc', 1),
                                array('normal', 0)
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'inc')
                            ),
                            'update_of_attribute_columns'=> array(
                                'INCREMENT' => array (
                                    array('inc', 1)
                                ),
                                'PUT' => array(
                                    array('normal', 1)
                                )
                            ),
                            'return_content' => array(
                                'return_type' => ReturnTypeConst::CONST_AFTER_MODIFY,
                                'return_column_names' => array('inc')
                            )
                        )
                    )
                )
            )
        );
        $response = $this->otsClient->batchWriteRow ($batchWrite1);
        $this->assertEquals($response['tables'][0]['rows'][0]['attribute_columns'][0][0], 'inc');
        $this->assertEquals($response['tables'][0]['rows'][0]['attribute_columns'][0][0], 'inc');
    }
    
    /*
     * DeleteOnlyInBatchWriteRow
     * BatchWriteRow包含4个Delete操作
     */
    public function testDeleteOnlyInBatchWriteRow() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name1'),
                                array('att2', -256.66)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name2'),
                                array('att2', -256.66)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name3'),
                                array('att2', -256.66)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name4'),
                                array('att2', -256.66)
                            )
                        )
                    )
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
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
                'table_name' => self::$usedTables[0],
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'max_versions' => 1,
                'columns_to_get' => array ('att2')
            );
            $a[] = $this->otsClient->getRow ($body);
        }
        $this->assertEquals (count ($a), 4);
        
        for($c = 0; $c < count ($a); $c ++) {
            $this->assertEmpty ($a[$c]['primary_key']);
            $this->assertEmpty ($a[$c]['attribute_columns']);
        }
    }
    
    /*
     * 4PutUpdateDeleteInBatchWriteRow
     * BatchWriteRow同时包含4个Put，4个Update和4个Delete操作
     */
    public function testPutUpdateDeleteInBatchWriteRow() {
        for($i = 1; $i < 9; $i ++) {
            $put[] = array (
                'operation_type' => OperationTypeConst::CONST_PUT,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', 'name{$i}'),
                    array('att2', 256)
                )
            );
        }
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => $put
                )
            )
        );
        // tables
        
        $this->otsClient->batchWriteRow ($batchWrite);
        $batchWrite1 = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  9),
                                array('PK2', 'a9')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  10),
                                array('PK2', 'a10')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  12),
                                array('PK2', 'a12')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  5),
                                array('PK2', 'a5')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  6),
                                array('PK2', 'a6')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  7),
                                array('PK2', 'a7')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  8),
                                array('PK2', 'a8')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
                            )
                        )
                    )
                )
            )
        );
        $getrow = $this->otsClient->batchWriteRow ($batchWrite1);
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'max_versions' => 1,
            'limit' => 100,
            'inclusive_start_primary_key' => array (
                array('PK1',  1),
                array('PK2', 'a1')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1',  30),
                array('PK2', 'a30')
            )
        );
        $a = $this->otsClient->getRange ($getRange);
        $this->assertEquals (8, count ($a['rows']));
        for($i = 0; $i < count ($a['rows']); $i ++) {
            $row = $a['rows'][$i];
            $this->assertEquals('PK1', $row['primary_key'][0][0]);
            $pk1 = $row['primary_key'][0][1];
            $columns = $row['attribute_columns'];
            $this->assertEquals ($pk1, $i + 5);
            // 1-4 rows deleted
            if ($pk1 >= 5 && $pk1 <= 8) {
                // 5-8 rows updated
                $this->assertEquals ($columns[0][1], 'Zhon');
            } elseif ($pk1 >= 9 && $pk1 <= 12) {
                // 9-12 rows put
                $this->assertEquals ($columns[0][1], 'name');
                $this->assertEquals ($columns[1][1], 256);
            } else {
                $this->fail ('Deleted rows read.');
            }
        }
    }
    
    /*
     * 1000PutUpdateDeleteInBatchWriteRow
     * BatchWriteRow同时包含300个Put，4个Update和4个Delete操作，期望返回服务端错误
     * 目前最多200个操作
     */
    public function testPut300UpdateDeleteInBatchWriteRow() {
        for($i = 1; $i < 300; $i ++) {
            $a[] = array (
                'operation_type' => OperationTypeConst::CONST_PUT,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', 'name'),
                    array('att2', 256)
                )
            );
        }
        // print_r($a);die;
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array_merge($a ,
                    array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  5),
                                array('PK2', 'a5')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  6),
                                array('PK2', 'a6')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  7),
                                array('PK2', 'a7')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  8),
                                array('PK2', 'a8')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        )
                    ,

                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  2),
                                array('PK2', 'a2')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  3),
                                array('PK2', 'a3')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  4),
                                array('PK2', 'a4')
                            )
                        )
                    ))
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'Rows count exceeds the upper limit: 200.';
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
                'table_meta' => array (
                    'table_name' => 'test' . $i,
                    'primary_key_schema' => array (
                        array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                        array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                    )
                ),
                'reserved_throughput' => array (
                    'capacity_unit' => array (
                        'read' => 0,
                        'write' => 0
                    )
                )
            );
            $this->otsClient->createTable ($tablebody);
        }
        $this->waitForTableReady ();
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => 'test1',
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        )
                    )
                ),
                array (
                    'table_name' => 'test2',
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        )
                    )
                ),
                array (
                    'table_name' => 'test3',
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        )
                    )
                ),
                array (
                    'table_name' => 'test4',
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  1),
                                array('PK2', 'a1')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWrite);
        for($i = 1; $i < 5; $i ++) {
            $body = array (
                'table_name' => 'test' . $i,
                'primary_key' => array (
                    array('PK1',  1),
                    array('PK2', 'a1')
                ),
                'max_versions' => 1,
                'columns_to_get' => array ()
            );
            $getrow[] = $this->otsClient->getRow ($body);
        }
        $primary = array (
            array('PK1',  1),
            array('PK2', 'a1')
        );
        $columns = array (
            array('att1', 'name'),
            array('att2', 256)
        );
        $this->assertEquals (count ($getrow), 4);
        for($i = 0; $i < count ($getrow); $i ++) {
            $this->assertEquals ($getrow[$i]['primary_key'], $primary);
            $this->assertColumnEquals($columns, $getrow[$i]['attribute_columns']);
        }
    }
    
    /*
     * OneTableOneFailInBatchWriteRow
     * BatchWriteRow有一个表中的一行失败的情况
     */
    public function testOneTableOneFailInBatchWriteRow() {
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  9),
                                array('PK2', 'a9')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  10),
                                array('PK2', 'a10')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => array (
                                array('PK1',  510),
                                array('PK2', 'a510')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  6),
                                array('PK2', 'a6')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  12),
                                array('PK2', 'a12')
                            )
                        )
                    )
                )
            )
        );
        $error = array (
            'code' => 'OTSConditionCheckFail',
            'message' => 'Condition check failed.'
        );
        $writerow = $this->otsClient->batchWriteRow ($batchWrite);
        $this->assertEquals (0, $writerow['tables'][0]['rows'][2]['is_ok']);
        $this->assertEquals ($error, $writerow['tables'][0]['rows'][2]['error']);
    }
    
    /*
     * OneTableTwoFailInBatchWriteRow
     * BatchWriteRow有一个表中的二行失败的情况
     */
    public function testOneTableTwoFailInBatchWriteRow() {
        $pkOfRows = array (
            array (
                array('PK1',  9),
                array('PK2', 'a9')
            ),
            array (
                array('PK1',  10),
                array('PK2', 'a10')
            ),
            array (
                array('PK1',  510),
                array('PK2', 'a510')
            ),
            array (
                array('PK1',  6),
                array('PK2', 'a6')
            ),
            array (
                array('PK1',  11),
                array('PK2', 'a11')
            ),
            array (
                array('PK1',  12),
                array('PK2', 'a12')
            )
        );
        
        foreach ($pkOfRows as $pk) {
            $this->otsClient->deleteRow (array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => $pk
            ));
        }
        
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  9),
                                array('PK2', 'a9')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  10),
                                array('PK2', 'a10')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => array (
                                array('PK1',  510),
                                array('PK2', 'a510')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => array (
                                array('PK1',  6),
                                array('PK2', 'a6')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),

                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        ),
                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  12),
                                array('PK2', 'a12')
                            )
                        )
                    )
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow ($batchWrite);
        $this->assertEquals ($writerow['tables'][0]['rows'][2]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['rows'][3]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['rows'][2]['error'], array (
            'code' => 'OTSConditionCheckFail',
            'message' => 'Condition check failed.'
        ));
        $this->assertEquals ($writerow['tables'][0]['rows'][3]['error'], array (
            'code' => 'OTSConditionCheckFail',
            'message' => 'Condition check failed.'
        ));
    }
    
    /*
     * TwoTableOneFailInBatchWriteRow
     * BatchWriteRow有2个表各有1行失败的情况
     */
    public function testTwoTableOneFailInBatchWriteRow() {
        $tables = $this->otsClient->listTable (array ());
        if (! in_array (self::$usedTables[1], $tables)) {
            $tablebody = array(
                'table_meta' => array(
                    'table_name' => self::$usedTables[1],
                    'primary_key_schema' => array(
                        array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                        array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                    )
                ),
                'reserved_throughput' => array(
                    'capacity_unit' => array(
                        'read' => 0,
                        'write' => 0
                    )
                )
            );
            $this->otsClient->createTable($tablebody);
            $this->waitForTableReady();
        }
        $batchWrite = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  9),
                                array('PK2', 'a9')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => array (
                                array('PK1',  510),
                                array('PK2', 'a510')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),

                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  9),
                                array('PK2', 'a9')
                            ),
                            'attribute_columns' => array (
                                array('att1', 'name'),
                                array('att2', 256)
                            )
                        )
                    ,
                    // //////添加多行插入 put_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                            'primary_key' => array (
                                array('PK1',  510),
                                array('PK2', 'a510')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('att1', 'Zhon')
                                ),
                                'DELETE_ALL' => array(
                                    'att2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow ($batchWrite);
        // print_r($writerow);die;
        $this->assertEquals ($writerow['tables'][0]['rows'][1]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][1]['rows'][1]['is_ok'], 0);
        $this->assertEquals ($writerow['tables'][0]['rows'][1]['error'], array (
            'code' => 'OTSConditionCheckFail',
            'message' => 'Condition check failed.'
        ));
        $this->assertEquals ($writerow['tables'][1]['rows'][1]['error'], array (
            'code' => 'OTSConditionCheckFail',
            'message' => 'Condition check failed.'
        ));
    }
    
    /*
     * 1000TablesInBatchWriteRow
     * BatchWriteRow包含1000个表的情况，期望返回服务端错误
     */
    public function testP1000TablesInBatchWriteRow() {
        for($i = 1; $i < 1001; $i ++) {
            $res[] = array (
                'table_name' => 'test' . $i,
                'rows' => array (
                    array (
                        'operation_type' => OperationTypeConst::CONST_PUT,
                        'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                        'primary_key' => array (
                            array('PK1',  1),
                            array('PK2', 'a1')
                        ),
                        'attribute_columns' => array (
                            array('att1', 'name'),
                            array('att2', 256)
                        )
                    )
                )
            );
        }
        $batchWrite = array (
            'tables' => $res
        );
        try {
            $this->otsClient->batchWriteRow ($batchWrite);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'Rows count exceeds the upper limit: 200.';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /**
     * 测试在单表上使用单一ColumnCondition的过滤条件下，使用BatchWriteRow接口进行批量写入操作是否成功。
     */
    public function testBatchWriteRowWithSingleColumnConditionAndSingleTable() {
        // To prepare the environment.
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (! in_array (self::$usedTables[$i], $tables)) {
                $tablemeta = array (
                    'table_meta' => array (
                        'table_name' => self::$usedTables[$i],
                        'primary_key_schema' => array (
                            array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                            array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                        )
                    ),
                    'reserved_throughput' => array (
                        'capacity_unit' => array (
                            'read' => 0,
                            'write' => 0
                        )
                    )
                );
                $this->otsClient->createTable ($tablemeta);
                $this->waitForTableReady ();
            }
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    'table_name' => self::$usedTables[$i],
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK1', $k),
                        array('PK2', 'a' . $k)
                    ),
                    'attribute_columns' => array (
                        array('attr1', $k),
                        array('attr2', 'aa'),
                        array('attr3', 'tas'),
                        array('attr4', $k . '-' . $k)
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        $this->waitForTableReady ();
        // begin testing
        $batchWriteData = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'column_name' => 'attr1',
                                    'value' => 19,
                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  19),
                                array('PK2', 'a19')
                            ),
                            'attribute_columns' => array (
                                array('attr1', 109),
                                array('attr2', 'aa109')
                            )
                        ),

                     //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                'column_condition' => array (
                                    'column_name' => 'attr1',
                                    'value' => 99,
                                    'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  99),
                                array('PK2', 'a99')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('attr1', 990)
                                ),
                                'DELETE_ALL' => array(
                                    'attr2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'column_name' => 'attr2',
                                    'value' => 'ab',
                                    'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        ),
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        $batchGetQuery = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'max_versions' => 1,
                    'columns_to_get' => array (
                        'attr1',
                        'attr2'
                    ),
                    'primary_keys' => array (
                        array (
                            array('PK1',  19),
                            array('PK2', 'a19')
                        ),
                        array (
                            array('PK1',  99),
                            array('PK2', 'a99')
                        ),
                        array (
                            array('PK1',  11),
                            array('PK2', 'a11')
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (3, count ($batchGetRes['tables'][0]['rows']));
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals (1, $batchGetRes['tables'][0]['rows'][$i]['is_ok']);
        }
        $row0 = array(
            array('attr1', 109),
            array('attr2', 'aa109')
        );
        $row1 = array(
            array('attr1', 990)
        );

        $this->assertColumnEquals($row0,  $batchGetRes['tables'][0]['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($row1, $batchGetRes['tables'][0]['rows'][1]['attribute_columns']);
        $this->assertEquals (0, count ($batchGetRes['tables'][0]['rows'][2]['attribute_columns']));
    }
    
    /**
     * 测试在单表中使用多重ColumnCondition的过滤条件下，使用BatchWriteRow接口进行批量写入操作是否成功。
     */
    public function testBatchWriteRowWithMultipleColumnConditionsAndSingleTables() {
        // To prepare the environment.
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (in_array (self::$usedTables[$i], $tables)) {
                $this->otsClient->deleteTable (array (
                    'table_name' => self::$usedTables[$i]
                ));
            }
            $tablemeta = array (
                'table_meta' => array (
                    'table_name' => self::$usedTables[$i],
                    'primary_key_schema' => array (
                        array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                        array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                    )
                ),
                'reserved_throughput' => array (
                    'capacity_unit' => array (
                        'read' => 0,
                        'write' => 0
                    )
                )
            );
            $this->otsClient->createTable ($tablemeta);
            $this->waitForTableReady ();
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    'table_name' => self::$usedTables[$i],
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK1', $k),
                        array('PK2', 'a' . $k)
                    ),
                    'attribute_columns' => array (
                        array('attr1', $k),
                        array('attr2', 'aa'),
                        array('attr3', 'tas'),
                        array('attr4', $k . '-' . $k)
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        // begin testing
        $batchWriteData = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_NOT,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 19,
                                            'comparator' => ComparatorTypeConst::CONST_NOT_EQUAL
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  19),
                                array('PK2', 'a19')
                            ),
                            'attribute_columns' => array (
                                array('attr1', 109),
                                array('attr2', 'aa109')
                            )
                        ),

                    // //////添加多行插入 put_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_AND,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 99,
                                            'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                        ),
                                        array (
                                            'logical_operator' => LogicalOperatorConst::CONST_OR,
                                            'sub_conditions' => array (
                                                array (
                                                    'column_name' => 'attr2',
                                                    'value' => 'aa',
                                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                                ),
                                                array (
                                                    'column_name' => 'attr2',
                                                    'value' => 'ddddd',
                                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  99),
                                array('PK2', 'a99')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('attr1', 990)
                                ),
                                'DELETE_ALL' => array(
                                    'attr2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'column_name' => 'attr2',
                                    'value' => 'ab',
                                    'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        
        $batchGetQuery = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'max_versions' => 1,
                    'columns_to_get' => array (
                        'attr1',
                        'attr2'
                    ),
                    'primary_keys' => array (
                        array (
                            array('PK1',  19),
                            array('PK2', 'a19')
                        ),
                        array (
                            array('PK1',  99),
                            array('PK2', 'a99')
                        ),
                        array (
                            array('PK1',  11),
                            array('PK2', 'a11')
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (3, count ($batchGetRes['tables'][0]['rows']));
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }
        $row0 = array(
            array('attr1', 109),
            array('attr2', 'aa109')
        );
        $row1 = array(
            array('attr1', 990)
        );

        $this->assertColumnEquals($row0,  $batchGetRes['tables'][0]['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($row1, $batchGetRes['tables'][0]['rows'][1]['attribute_columns']);
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows'][2]['attribute_columns']), 0);
    }
    
    /**
     * 测试在多表中和单一ColumnCondition的过滤条件下，使用BatchWriteRow接口进行批量写入的操作是否成功。
     */
    public function testBatchWriteRowWithSingleColumnConditionAndMultipleTables() {
        // To prepare the environment.
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (! in_array (self::$usedTables[$i], $tables)) {
                $tablemeta = array (
                    'table_meta' => array (
                        'table_name' => self::$usedTables[$i],
                        'primary_key_schema' => array (
                            array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                            array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                        )
                    ),
                    'reserved_throughput' => array (
                        'capacity_unit' => array (
                            'read' => 0,
                            'write' => 0
                        )
                    )
                );
                $this->otsClient->createTable ($tablemeta);
                $this->waitForTableReady ();
            }
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    'table_name' => self::$usedTables[$i],
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK1', $k),
                        array('PK2', 'a' . $k)
                    ),
                    'attribute_columns' => array (
                        array('attr1', $k),
                        array('attr2', 'aa'),
                        array('attr3', 'tas'),
                        array('attr4', $k . '-' . $k)
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        // begin testing
        $batchWriteData = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'column_name' => 'attr1',
                                    'value' => 19,
                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  19),
                                array('PK2', 'a19')
                            ),
                            'attribute_columns' => array (
                                array('attr1', 109),
                                array('attr2', 'aa109')
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                'column_condition' => array (
                                    'column_name' => 'attr1',
                                    'value' => 99,
                                    'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  99),
                                array('PK2', 'a99')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('attr1', 990)
                                ),
                                'DELETE_ALL' => array(
                                    'attr2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'column_name' => 'attr2',
                                    'value' => 'ab',
                                    'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE
                            ),
                            'primary_key' => array (
                                array('PK1',  119),
                                array('PK2', 'a119')
                            ),
                            'attribute_columns' => array (
                                array('attr1', 119),
                                array('attr2', 'aa119')
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_AND,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 10,
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            'column_name' => 'attr2',
                                            'value' => 'aa',
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  10),
                                array('PK2', 'a10')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('attr1', 1000)
                                ),
                                'DELETE_ALL' => array(
                                    'attr2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_OR,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 11,
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            'column_name' => 'attr2',
                                            'value' => 'aabbb',
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        
        $batchGetQuery = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'max_versions' => 1,
                    'columns_to_get' => array (
                        'attr1',
                        'attr2'
                    ),
                    'primary_keys' => array (
                        array (
                            array('PK1',  19),
                            array('PK2', 'a19')
                        ),
                        array (
                            array('PK1',  99),
                            array('PK2', 'a99')
                        ),
                        array (
                            array('PK1',  11),
                            array('PK2', 'a11')
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1],
                    'max_versions' => 1,
                    'columns_to_get' => array (
                        'attr1',
                        'attr2'
                    ),
                    'primary_keys' => array (
                        array (
                            array('PK1',  119),
                            array('PK2', 'a119')
                        ),
                        array (
                            array('PK1',  10),
                            array('PK2', 'a10')
                        ),
                        array (
                            array('PK1',  11),
                            array('PK2', 'a11')
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        
        // to verify the first updated table
        $this->assertEquals (3, count ($batchGetRes['tables'][0]['rows']));
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }
        $row0 = array(
            array('attr1', 109, 'INTEGER'),
            array('attr2', 'aa109', 'STRING')
        );
        $row1 = array(
            array('attr1', 990, 'INTEGER')
        );

        $this->assertColumnEquals($row0, $batchGetRes['tables'][0]['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($row1, $batchGetRes['tables'][0]['rows'][1]['attribute_columns']);
        
        $this->assertEquals (count ($batchGetRes['tables'][0]['rows'][2]['attribute_columns']), 0);
        
        // to verify the second updated table
        $this->assertEquals (count ($batchGetRes['tables'][1]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][1]['rows'][$i]['is_ok'], 1);
        }
        $row0 = array(
            array('attr1', 119),
            array('attr2', 'aa119')
        );
        $row1 = array(
            array('attr1', 1000)
        );

        $this->assertColumnEquals($row0,  $batchGetRes['tables'][1]['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($row1,  $batchGetRes['tables'][1]['rows'][1]['attribute_columns']);
        
        $this->assertEquals (count ($batchGetRes['tables'][1]['rows'][2]['attribute_columns']), 0);
    }
    
    /**
     * 测试在多表中使用多重ColumnCondition过滤条件下，使用BatchWriteRow接口进行批量写入是否成功。
     */
    public function testBatchWriteRowWithMultipleColumnConditionsAndMultipleTables() {
        // To prepare the environment.
        $tables = $this->otsClient->listTable (array ());
        for($i = 0; $i < 2; ++ $i) {
            if (in_array (self::$usedTables[$i], $tables)) {
                $this->otsClient->deleteTable (array (
                    'table_name' => self::$usedTables[$i]
                ));
            }
            $tablemeta = array (
                'table_meta' => array (
                    'table_name' => self::$usedTables[$i],
                    'primary_key_schema' => array (
                        array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                        array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                    )
                ),
                'reserved_throughput' => array (
                    'capacity_unit' => array (
                        'read' => 0,
                        'write' => 0
                    )
                )
            );
            $this->otsClient->createTable ($tablemeta);
            $this->waitForTableReady ();
            for($k = 1; $k < 100; ++ $k) {
                $putdata = array (
                    'table_name' => self::$usedTables[$i],
                    'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                    'primary_key' => array (
                        array('PK1', $k),
                        array('PK2', 'a' . $k)
                    ),
                    'attribute_columns' => array (
                        array('attr1', $k),
                        array('attr2', 'aa'),
                        array('attr3', 'tas'),
                        array('attr4', $k . '-' . $k)
                    )
                );
                $this->otsClient->putRow ($putdata);
            }
        }
        // begin testing
        $batchWriteData = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_NOT,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 19,
                                            'comparator' => ComparatorTypeConst::CONST_NOT_EQUAL
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  19),
                                array('PK2', 'a19')
                            ),
                            'attribute_columns' => array (
                                array('attr1', 109),
                                array('attr2', 'aa109')
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_AND,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 99,
                                            'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                        ),
                                        array (
                                            'logical_operator' => LogicalOperatorConst::CONST_OR,
                                            'sub_conditions' => array (
                                                array (
                                                    'column_name' => 'attr2',
                                                    'value' => 'aa',
                                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                                ),
                                                array (
                                                    'column_name' => 'attr2',
                                                    'value' => 'ddddd',
                                                    'comparator' => ComparatorTypeConst::CONST_EQUAL
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  99),
                                array('PK2', 'a99')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('attr1', 990)
                                ),
                                'DELETE_ALL' => array(
                                    'attr2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'column_name' => 'attr2',
                                    'value' => 'ab',
                                    'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1],
                    'rows' => array (
                        array (
                            'operation_type' => OperationTypeConst::CONST_PUT,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE
                            ),
                            'primary_key' => array (
                                array('PK1',  119),
                                array('PK2', 'a119')
                            ),
                            'attribute_columns' => array (
                                array('attr1', 119),
                                array('attr2', 'aa119')
                            )
                        ),

                    // //////添加多行插入 update_rows

                        array (
                            'operation_type' => OperationTypeConst::CONST_UPDATE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_AND,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 10,
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            'column_name' => 'attr2',
                                            'value' => 'aa',
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  10),
                                array('PK2', 'a10')
                            ),
                            'update_of_attribute_columns'=> array(
                                'PUT' => array (
                                    array('attr1', 1000)
                                ),
                                'DELETE_ALL' => array(
                                    'attr2'
                                )
                            )
                        ),


                        array (
                            'operation_type' => OperationTypeConst::CONST_DELETE,
                            'condition' => array (
                                'row_existence' => RowExistenceExpectationConst::CONST_IGNORE,
                                'column_condition' => array (
                                    'logical_operator' => LogicalOperatorConst::CONST_OR,
                                    'sub_conditions' => array (
                                        array (
                                            'column_name' => 'attr1',
                                            'value' => 11,
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        ),
                                        array (
                                            'column_name' => 'attr2',
                                            'value' => 'aabbb',
                                            'comparator' => ComparatorTypeConst::CONST_EQUAL
                                        )
                                    )
                                )
                            ),
                            'primary_key' => array (
                                array('PK1',  11),
                                array('PK2', 'a11')
                            )
                        )
                    )
                )
            )
        );
        $this->otsClient->batchWriteRow ($batchWriteData);
        
        $batchGetQuery = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'max_versions' => 1,
                    'columns_to_get' => array (
                        'attr1',
                        'attr2'
                    ),
                    'primary_keys' => array (
                        array (
                            array('PK1',  19),
                            array('PK2', 'a19')
                        ),
                        array (
                            array('PK1',  99),
                            array('PK2', 'a99')
                        ),
                        array (
                            array('PK1',  11),
                            array('PK2', 'a11')
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1],
                    'max_versions' => 1,
                    'columns_to_get' => array (
                        'attr1',
                        'attr2'
                    ),
                    'primary_keys' => array (
                        array (
                            array('PK1',  119),
                            array('PK2', 'a119')
                        ),
                        array (
                            array('PK1',  10),
                            array('PK2', 'a10')
                        ),
                        array (
                            array('PK1',  11),
                            array('PK2', 'a11')
                        )
                    )
                )
            )
        );
        $batchGetRes = $this->otsClient->batchGetRow ($batchGetQuery);
        
        // to verify the first updated table
        $this->assertEquals (3, count ($batchGetRes['tables'][0]['rows']));
        for($i = 0; $i < count ($batchGetRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][0]['rows'][$i]['is_ok'], 1);
        }

        $row0 = array(
            array('attr1', 109),
            array('attr2', 'aa109')
        );
        $row1 = array(
            array('attr1', 990)
        );

        $this->assertColumnEquals($row0,  $batchGetRes['tables'][0]['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($row1, $batchGetRes['tables'][0]['rows'][1]['attribute_columns']);
        $this->assertEquals (0, count ($batchGetRes['tables'][0]['rows'][2]['attribute_columns']));

        
        // to verify the second updated table
        $this->assertEquals (count ($batchGetRes['tables'][1]['rows']), 3);
        for($i = 0; $i < count ($batchGetRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetRes['tables'][1]['rows'][$i]['is_ok'], 1);
        }

        $row0 = array(
            array('attr1', 119),
            array('attr2', 'aa119')
        );
        $row1 = array(
            array('attr1', 1000)
        );

        $this->assertColumnEquals($row0,  $batchGetRes['tables'][1]['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($row1, $batchGetRes['tables'][1]['rows'][1]['attribute_columns']);
        $this->assertEquals (0, count ($batchGetRes['tables'][1]['rows'][2]['attribute_columns']));
    }
}

