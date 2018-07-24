<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\LogicalOperatorConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class BatchGetRowTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable',
        'myTable1',
        'test8',
        'test9'
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

    public function testmes() {
        $tablename = array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('attr1', 1),
                array('attr2', 'aa'),
                array('attr3', 'tas'),
                array('attr4', 11)
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
            $c = 'No row specified in the request of BatchGetRow.';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * EmptyBatchGetRow
     * BatchGetRow没有包含任何表的情况。
     * NOTE: 目前的行为是返回空行回来，退化成无结果的情况。
     */
    public function testEmpty1BatchGetRow() {
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[2]
                ),
                array (
                    'table_name' => self::$usedTables[3]
                )
            )
        );
        // print_r();die;
        try {
            $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGet);
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 0);
            $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows']), 0);
//            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
//            $c = 'No row specified in table: '' . self::$usedTables[2] . ''.';
//            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * 4ItemInBatchGetRow
     * BatchGetRow包含4个行。
     */
    public function testItemInBatchGetRow() {
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    )
                )
            )
        );
        
        $getrow = $this->otsClient->batchGetRow ($batchGet);
        for($i = 0; $i < count ($batchGet['tables'][0]['primary_keys']); $i ++) {
            $this->assertEquals ($getrow['tables'][0]['rows'][$i]['primary_key'], $batchGet['tables'][0]['primary_keys'][$i]);
        }
        // print_r($getrow);die;
    }
    
    /**
     * EmptyTableInBatchGetRow
     * BatchGetRow包含2个表，其中有1个表有1行，另外一个表为空的情况。// 不会抛出异常
     * NOTE: 目前不会抛异常，只会返回空结果
     */
    public function testEmptyTableInBatchGetRow() {
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1]
                )
            )
        );
        try {
            $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGet);
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows']), 0);
//            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
//            $c = 'No row specified in table: '' . self::$usedTables[1] . ''.';
//            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /**
     * 1000ItemInBatchGetRow
     * BatchGetRow包含1000个行，期望返回服务端错误？ 上限100
     */
    public function testItemIn1000BatchGetRow() {
        for($i = 0; $i < 200; $i ++) {
            $a[] = array (
                array('PK1', $i),
                array('PK2', 'a' . $i)
            );
        }
        // print_r($a);die;
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => $a
                )
            )
        );
        try {
            $this->otsClient->batchGetRow ($batchGet);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'Rows count exceeds the upper limit: 100.';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * OneTableOneFailInBatchGetRow
     * BatchGetRow有一个表中的一行失败的情况
     * NOTE: 目前出错提示更详尽了。
     */
    public function testOneTableOneFailInBatchGetRow() {
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK11', 3),
                            array('PK12', 'a3')
                        )
                    )
                )
            )
        );
        $getrow = $this->otsClient->batchGetRow ($batchGet);
        if (is_array ($getrow)) {
            // print_r($getrow);die;
            $this->assertEquals ($getrow['tables'][0]['rows'][0]['primary_key'], $batchGet['tables'][0]['primary_keys'][0]);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['primary_key'], $batchGet['tables'][0]['primary_keys'][1]);
            $this->assertEquals ($getrow['tables'][0]['rows'][2]['is_ok'], 0);
            $error = array (
                'code' => 'OTSInvalidPK',
                'message' => 'Validate PK name fail. Input: PK11, Meta: PK1.'
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
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK11', 2),
                            array('PK22', 'a2')
                        ),
                        array (
                            array('PK11', 3),
                            array('PK12', 'a3')
                        )
                    )
                )
            )
        );
        if (is_array ($this->otsClient->batchGetRow ($batchGet))) {
            $getrow = $this->otsClient->batchGetRow ($batchGet);
            // print_r($getrow);die;
            // print_r($getrow);die;
            $this->assertEquals ($getrow['tables'][0]['rows'][0]['primary_key'], $batchGet['tables'][0]['primary_keys'][0]);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['is_ok'], 0);
            $this->assertEquals ($getrow['tables'][0]['rows'][2]['is_ok'], 0);
            $error = array (
                'code' => 'OTSInvalidPK',
                'message' => 'Validate PK name fail. Input: PK11, Meta: PK1.'
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
        for($i = 1; $i < 10; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[1],
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
        $table = array (
            'table_name' => self::$usedTables[1],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('attr1', 1),
                array('attr2', 'a1')
            )
        );
        $this->waitForTableReady ();
        $this->otsClient->putRow ($table);
        $batchGet = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK11', 2),
                            array('PK22', 'a2')
                        )
                    )
                ),
                array (
                    'table_name' => self::$usedTables[1],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK11', 2),
                            array('PK22', 'a2')
                        )
                    )
                )
            )
        );
        $getrow = $this->otsClient->batchGetRow ($batchGet);
        if (is_array ($getrow)) {
            $error = array (
                'code' => 'OTSInvalidPK',
                'message' => 'Validate PK name fail. Input: PK11, Meta: PK1.'
            );
            // print_r($getrow);die;
            $this->assertEquals ($getrow['tables'][0]['rows'][0]['primary_key'], $batchGet['tables'][0]['primary_keys'][0]);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['is_ok'], 0);
            $this->assertEquals ($getrow['tables'][0]['rows'][1]['error'], $error);
            $this->assertEquals ($getrow['tables'][1]['rows'][0]['primary_key'], $batchGet['tables'][1]['primary_keys'][0]);
            $this->assertEquals ($getrow['tables'][1]['rows'][1]['is_ok'], 0);
            $this->assertEquals ($getrow['tables'][1]['rows'][1]['error'], $error);
        }
    }
    
    /**
     * 测试在单表中和单一ColumnCondition过滤条件下，使用BatchGetRow接口进行批量读取数据的操作是否成功。
     */
    public function testSingleTableBatchGetRowWithSingleCondition() {
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $batchGetQuery = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr1',
                                'value' => 1,
                                'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                'column_name' => 'attr2',
                                'value' => 'a6',
                                'comparator' => ComparatorTypeConst::CONST_LESS_THAN
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
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['primary_key'][0], array('PK1', $i + 1));
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['primary_key'][1], array('PK2', 'a' . ($i + 1)));
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['attribute_columns'][0][1], $i + 1);
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['attribute_columns'][1][1], 'a' . ($i + 1));
        }
        
        $batchGetQuery2 = array (
            'tables' => array (
                array (
                    'table_name' => self::$usedTables[0],
                    'columns_to_get' => array (),
                    'max_versions' => 1,
                    'primary_keys' => array (
                        array (
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'column_name' => 'attr1',
                        'value' => 100,
                        'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                    )
                )
            )
        );
        $batchGetQueryRes2 = $this->otsClient->batchGetRow ($batchGetQuery2);
        
        $this->assertEquals (count ($batchGetQueryRes2['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes2['tables'][0]['rows']); $i ++) {
            $this->assertEquals (1, $batchGetQueryRes2['tables'][0]['rows'][$i]['is_ok']);
            $this->assertEquals (0, count ($batchGetQueryRes2['tables'][0]['rows'][$i]['attribute_columns']));
        }
    }
    
    /**
     * 测试在单表中使用多重ColumnCondition过滤条件下，使用BatchGetRow接口进行批量数据读取的操作是否成功。
     */
    public function testSingleTableBatchGetRowWithMultipleCondition() {
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
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
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr1',
                                'value' => 1,
                                'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                'column_name' => 'attr2',
                                'value' => 'a6',
                                'comparator' => ComparatorTypeConst::CONST_LESS_THAN
                            ),
                            array (
                                'logical_operator' => LogicalOperatorConst::CONST_OR,
                                'sub_filters' => array (
                                    array (
                                        'column_name' => 'attr1',
                                        'value' => 100,
                                        'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                    ),
                                    array (
                                        'column_name' => 'attr2',
                                        'value' => 'a0',
                                        'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
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
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows'][$i]['attribute_columns']), 0);
        }
    }
    
    /**
     * 测试在多表中和单一ColumnCondition过滤条件下，使用BatchGetRow接口进行批量数据读取操作是否成功。
     */
    public function testMultipleTablesBatchGetRowWithSingleCondition() {
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $allTables = $this->otsClient->listTable (array ());
        if (! in_array (self::$usedTables[1], $allTables))
            $this->otsClient->createTable (array (
                'table_meta' => array (
                    'table_name' => self::$usedTables[1],
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
            ));
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[1],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        
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
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr1',
                                'value' => 1,
                                'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                'column_name' => 'attr2',
                                'value' => 'a6',
                                'comparator' => ComparatorTypeConst::CONST_LESS_THAN
                            ),
                            array (
                                'logical_operator' => LogicalOperatorConst::CONST_OR,
                                'sub_filters' => array (
                                    array (
                                        'column_name' => 'attr1',
                                        'value' => 100,
                                        'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                    ),
                                    array (
                                        'column_name' => 'attr2',
                                        'value' => 'a0',
                                        'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                                    )
                                )
                            )
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
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 1),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'column_name' => 'attr1',
                        'value' => 3,
                        'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                    )
                )
            )
        );
        $batchGetQueryRes = $this->otsClient->batchGetRow ($batchGetQuery);
        $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][0]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][0]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows'][$i]['attribute_columns']), 0);
        }
        $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][1]['rows'][$i]['is_ok'], 1);
            if ($i < 2)
                $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows'][$i]['attribute_columns']), 0);
            else {
                $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows'][$i]['attribute_columns']), 2);
            }
        }
    }
    
    /**
     * 测试在多表中和多重ColumnCondition过滤条件下，使用BatchGetRow接口进行批量数据读取的操作是否成功。
     */
    public function testMultipleTablesBatchGetRowWithMultipleConditions() {
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        $allTables = $this->otsClient->listTable (array ());
        if (! in_array (self::$usedTables[1], $allTables))
            $this->otsClient->createTable (array (
                'table_meta' => array (
                    'table_name' => self::$usedTables[1],
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
            ));
        for($i = 1; $i < 100; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[1],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('attr1', $i),
                    array('attr2', 'a' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        
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
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr1',
                                'value' => 1,
                                'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                'column_name' => 'attr2',
                                'value' => 'a6',
                                'comparator' => ComparatorTypeConst::CONST_LESS_THAN
                            ),
                            array (
                                'logical_operator' => LogicalOperatorConst::CONST_OR,
                                'sub_filters' => array (
                                    array (
                                        'column_name' => 'attr1',
                                        'value' => 100,
                                        'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                                    ),
                                    array (
                                        'column_name' => 'attr2',
                                        'value' => 'a0',
                                        'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                                    )
                                )
                            )
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
                            array('PK1', 1),
                            array('PK2', 'a1')
                        ),
                        array (
                            array('PK1', 2),
                            array('PK2', 'a2')
                        ),
                        array (
                            array('PK1', 3),
                            array('PK2', 'a3')
                        ),
                        array (
                            array('PK1', 4),
                            array('PK2', 'a4')
                        )
                    ),
                    'column_filter' => array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr1',
                                'value' => 3,
                                'comparator' => ComparatorTypeConst::CONST_GREATER_EQUAL
                            ),
                            array (
                                'logical_operator' => LogicalOperatorConst::CONST_NOT,
                                'sub_filters' => array (
                                    array (
                                        'column_name' => 'attr2',
                                        'value' => 'a9',
                                        'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
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
            $this->assertEquals (count ($batchGetQueryRes['tables'][0]['rows'][$i]['attribute_columns']), 0);
        }
        $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows']), 4);
        for($i = 0; $i < count ($batchGetQueryRes['tables'][1]['rows']); $i ++) {
            $this->assertEquals ($batchGetQueryRes['tables'][1]['rows'][$i]['is_ok'], 1);
            $this->assertEquals (count ($batchGetQueryRes['tables'][1]['rows'][$i]['attribute_columns']), 0);
        }
    }
}

