<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\LogicalOperatorConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\DirectionConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class GetRangeTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable',
        'myTablexx',
        'myTablexx2',
        'myTable1'
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
            )
        ));

        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[1],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        ));

        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[2],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        ));

        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[3],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK2', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK3', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK4', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
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
     * GetRangeForward
     * 先写入两行，PK分别为1和2，GetRange，方向为Forward，期望顺序得到1和2两行。
     */
    public function testGetRangeForward() {

        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'limit' => 10,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            )
        );
        $rowone = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 1),
                array('att2', 'att1')
            )
        );
        $rowtwo = array (
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2')
            ),
            'attribute_columns' => array (
                array('att1', 2),
                array('att2', 'att2')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $this->assertEmpty ($tables['next_start_primary_key']);
        $this->assertRowEquals ($rowone, $tables['rows'][0]);
        $this->assertRowEquals ($rowtwo, $tables['rows'][1]);
    }
    
    /*
     *
     * GetRangeBackward
     * 先写入两行，PK分别为1和2，GetRange，方向为Backward，期望顺序得到2和1两行。
     */
    public function testGetRangeBackward() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'max_versions' => 1,
            'direction' => DirectionConst::CONST_BACKWARD,
            'limit' => 10,
            'inclusive_start_primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 0),
                array('PK2', 'a0')
            )
        );
        $rowone = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 1),
                array('att2', 'att1')
            )
        );
        $rowtwo = array (
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2')
            ),
            'attribute_columns' => array (
                array('att1', 2),
                array('att2', 'att2')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        // print_r($tables);die;
        $this->assertEmpty ($tables['next_start_primary_key']);
        $this->assertRowEquals ($rowtwo, $tables['rows'][0]);
        $this->assertRowEquals ($rowone, $tables['rows'][1]);
    }
    
    /*
     *
     * InfMinInRange
     * 先写入2行，PK分别为1和2，GetRange，方向为Forward，范围为 [INF_MIN, 2) 或者 [1, INF_MIN)，期望顺序得到0和1两行，或者返回服务端错误
     */
    public function testInfMinInRange() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 10,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MIN),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MIN)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 10),
                array('PK2', 'a10')
            )
        );
        $rowone = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 1),
                array('att2', 'att1')
            )
        );
        $rowtwo = array (
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2')
            ),
            'attribute_columns' => array (
                array('att1', 2),
                array('att2', 'att2')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        // print_r($tables);die;
        $this->assertEmpty ($tables['next_start_primary_key']);
        $this->assertRowEquals ($rowone, $tables['rows'][0]);
        $this->assertRowEquals ($rowtwo, $tables['rows'][1]);
    }
    
    /*
     *
     * InfMinInRange
     * 先写入2行，PK分别为0, 1和2，GetRange，方向为Backward，范围为 [INF_MAX, 2) 或者 [1, INF_MAX)，顺序得到2和1两行
     */
    public function testInfMaxInRange() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_BACKWARD,
            'limit' => 10,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 0),
                array('PK2', 'a0')
            )
        );
        $rowone = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 1),
                array('att2', 'att1')
            )
        );
        $rowtwo = array (
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2')
            ),
            'attribute_columns' => array (
                array('att1', 2),
                array('att2', 'att2')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        // print_r($tables);die;
        $this->assertEmpty ($tables['next_start_primary_key']);
        $this->assertRowEquals ($rowtwo, $tables['rows'][0]);
        $this->assertRowEquals ($rowone, $tables['rows'][1]);
    }
    
    /*
     *
     * GetRangeWithDefaultColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRange请求ColumnsToGet参数属性列，期望读出所有4个属性列。
     */
    public function testGetRangeWithDefaultColumnsToGet() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[3],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i),
                    array('PK3', $i),
                    array('PK4', 'b' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i),
                    array('att3', $i),
                    array('att4', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[3],
            'direction' => DirectionConst::CONST_BACKWARD,
            'max_versions' => 1,
            'columns_to_get' => array (
                'att1',
                'att2',
                'att3',
                'att4'
            ),
            'limit' => 10,
            'inclusive_start_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK3', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK4', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 0),
                array('PK2', 'a0'),
                array('PK3', 1),
                array('PK4', 'a0')
            )
        );
        $rowone = // 'primary_key' => array('PK1' => 1, 'PK2' => 'a1'),
        array (
            array('att1', 2),
            array('att2', 'att2'),
            array('att3', 2),
            array('att4', 'att2')
        );
        $rowtwo = // 'primary_key' => array('PK1' => 2, 'PK2' => 'a2'),
        array (
            array('att1', 1),
            array('att2', 'att1'),
            array('att3', 1),
            array('att4', 'att1')
        );
        $tables = $this->otsClient->getRange ($getRange);
        // print_r($tables);die;
        $this->assertEmpty ($tables['next_start_primary_key']);
        // 目前设置读取属性列还是会有主键列的返回的
//        $this->assertEmpty ($tables['rows'][0]['primary_key']);
//        $this->assertEmpty ($tables['rows'][1]['primary_key']);
        $this->assertColumnEquals($rowone, $tables['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($rowtwo, $tables['rows'][1]['attribute_columns']);
    }
    
    /*
     *
     * GetRangeWith0ColumsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRange请求ColumnsToGet为空数组，期望读出所有数据。
     */
    public function testGetRangeWith0ColumsToGet() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[3],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i),
                    array('PK3', $i),
                    array('PK4', 'b' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i),
                    array('att3', $i),
                    array('att4', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[3],
            'direction' => DirectionConst::CONST_BACKWARD,
            'columns_to_get' => array (),
            'max_versions' => 1,
            'limit' => 10,
            'inclusive_start_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK3', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK4', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 0),
                array('PK2', 'a0'),
                array('PK3', 1),
                array('PK4', 'a0')
            )
        );
        $rowone = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1'),
                array('PK3', 1),
                array('PK4', 'b1')
            ),
            'attribute_columns' => array (
                array('att1', 1),
                array('att2', 'att1'),
                array('att3', 1),
                array('att4', 'att1')
            )
        );
        $rowtwo = array (
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2'),
                array('PK3', 2),
                array('PK4', 'b2')
            ),
            'attribute_columns' => array (
                array('att1', 2),
                array('att2', 'att2'),
                array('att3', 2),
                array('att4', 'att2')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $this->assertEmpty ($tables['next_start_primary_key']);
        $this->assertRowEquals ($rowtwo, $tables['rows'][0]);
        $this->assertRowEquals ($rowone, $tables['rows'][1]);
    }
    
    /*
     *
     * GetRangeWith4ColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRange请求ColumnsToGet包含其中2个主键列，2个属性列，期望返回数据包含参数中指定的列。
     * NOTE: 目前主键是完全返回的。
     */
    public function testGetRangeWith4ColumnsToGet() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[3],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i),
                    array('PK3', $i),
                    array('PK4', 'b' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i),
                    array('att3', $i),
                    array('att4', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[3],
            'direction' => DirectionConst::CONST_BACKWARD,
            'max_versions' => 1,
            'columns_to_get' => array (
                'PK1',
                'PK2',
                'att1',
                'att2'
            ),
            'limit' => 10,
            'inclusive_start_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK3', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK4', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 0),
                array('PK2', 'a0'),
                array('PK3', 1),
                array('PK4', 'a0')
            )
        );
        $rowone = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1'),
                array('PK3', 1),
                array('PK4', 'b1')
            ),
            'attribute_columns' => array (
                array('att1', 1),
                array('att2', 'att1')
            )
        );
        $rowtwo = array (
            'primary_key' => array (
                array('PK1', 2),
                array('PK2', 'a2'),
                array('PK3', 2),
                array('PK4', 'b2')
            ),
            'attribute_columns' => array (
                array('att1', 2),
                array('att2', 'att2')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $this->assertEmpty ($tables['next_start_primary_key']);
        $this->assertRowEquals ($rowtwo, $tables['rows'][0]);
        $this->assertRowEquals ($rowone, $tables['rows'][1]);
    }
    
    /*
     *
     * GetRangeWith1000ColumnsToGet
     * GetRange请求ColumnsToGet包含1000个不重复的列名，期望返回服务端错误？
     */
    public function testGetRangeWith1000ColumnsToGet() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        for($i = 0; $i < 1001; $i ++) {
            $a[] = 'a' . $i;
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => $a,
            'max_versions' => 1,
            'limit' => 10,
            'inclusive_start_primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            )
        );
        
        $this->otsClient->getRange ($getRange);
    }
    
    /*
     *
     * GetRangeWithDuplicateColumnsToGet
     * GetRange请求ColumnsToGet包含2个重复的列名，成功读取指定信息
     */
    public function testGetRangeWithDuplicateColumnsToGet() {
        for($i = 1; $i < 3; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (
                'att1',
                'att1'
            ),
            'limit' => 10,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', 3),
                array('PK2', 'a3')
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $this->assertEmpty ($tables['next_start_primary_key']);
        // NOTE: pk是一直有的
//        $this->assertEmpty ($tables['rows'][0]['primary_key']);
//        $this->assertEmpty ($tables['rows'][1]['primary_key']);
        $col1 = array(array('att1', 1));
        $col2 = array(array('att1', 2));
        $this->assertColumnEquals($col1, $tables['rows'][0]['attribute_columns']);
        $this->assertColumnEquals($col2, $tables['rows'][1]['attribute_columns']);
    }
    
    /*
     *
     * GetRangeWithLimit10
     * 先写入20行，GetRange Limit=10，期望返回10行，并校验 NextPK。
     */
    public function testGetRangeWithLimit10() {
        for($i = 1; $i < 21; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 10,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        for($i = 1; $i < 11; $i ++) {
            $row[] = array (
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i)
                )
            );
        }
        for($i = 0; $i < count ($tables['rows']); $i ++) {
            $this->assertRowEquals ($row[$i], $tables['rows'][$i]);
        }
        $primary = array (
            array('PK1', 11),
            array('PK2', 'a11')
        );
        $this->assertEquals ($tables['next_start_primary_key'], $primary);
        $this->assertEquals (count ($tables['rows']), 10);
    }
    
    /*
     *
     * GetRangeIteratorWith1Row
     * GetRangeIterator 返回1行的情况。
     */
    public function testGetRangeIteratorWith1Row() {
        for($i = 1; $i < 2; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i),
                    array('PK2', 'a' . $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i)
                )
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[0],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 1,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX),
                array('PK2', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $row = array (
            'primary_key' => array (
                array('PK1', 1),
                array('PK2', 'a1')
            ),
            'attribute_columns' => array (
                array('att1', 1)
            )
        );
        $this->assertRowEquals($row, $tables['rows'][0]);
    }


    /*
     *
     * GetRangeIteratorWith5000Rows
     * GetRangeIterator 返回5000行的情况，这时正好不发生第二次GetRange。
     */
    public function testGetRangeIteratorWith5000Rows() {
        for($i = 1; $i < 5001; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[1],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i)
                ),
                'attribute_columns' => array ()
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[1],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $this->assertEmpty ($tables['next_start_primary_key']);
    }
    
    /*
     *
     * GetRangeIteratorWith5001Rows
     * GetRangeIterator 返回5001行的情况，这时正好发生第二次GetRange。
     */
    public function testGetRangeIteratorWith5001Rows() {
        for($i = 1; $i < 5002; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[1],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i)
                ),
                'attribute_columns' => array ()
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[1],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $tables = $this->otsClient->getRange ($getRange);
        $this->assertNotEmpty ($tables['next_start_primary_key']);
    }
    
    /*
     *
     * GetRangeIteratorWith15001Rows
     * GetRangeIterator 返回15001行的情况，这时共发生4次GetRange。
     */
    public function testGetRangeIteratorWith15001Rows() {
        for($i = 1; $i < 15001; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[1],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i)
                ),
                'attribute_columns' => array ()
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[1],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)

            )
        );
        $this->otsClient->getRange ($getRange);
        $getRange1 = array (
            'table_name' => self::$usedTables[1],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 5001)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $this->otsClient->getRange ($getRange1);
        $getRange2 = array (
            'table_name' => self::$usedTables[1],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 10001)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $this->otsClient->getRange ($getRange2);
        $getRange3 = array (
            'table_name' => self::$usedTables[1],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 15001)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $tables = $this->otsClient->getRange ($getRange3);
        $this->assertEmpty ($tables['next_start_primary_key']);
    }
    
    /*
     *
     * GetRangeWithDefaultLimit
     * 先写入10000行，GetRange Limit为默认，期望2次返回全部行。
     */
    public function testGetRangeWithDefaultLimit() {
        for($i = 1; $i < 10001; $i ++) {
            $tablename = array (
                'table_name' => self::$usedTables[2],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i)
                ),
                'attribute_columns' => array ()
            );
            $this->otsClient->putRow ($tablename);
        }
        $getRange = array (
            'table_name' => self::$usedTables[2],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $this->otsClient->getRange ($getRange);
        // $primary = array('PK1' => 5001);
        // $this->assertEquals($tables['next_start_primary_key'], $primary);
        $getRange1 = array (
            'table_name' => self::$usedTables[2],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 5001)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            )
        );
        $tables = $this->otsClient->getRange ($getRange1);
        $this->assertEmpty ($tables['next_start_primary_key']);
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件下，使用GetRange扫描数据是否成功。
     */
    public function testGetGetRangeWithSingleCompositeCondition() {
        for($i = 1; $i < 10001; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[2],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        
        $getRange = array (
            'table_name' => self::$usedTables[2],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (
                'att1',
                'att2'
            ),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            ),
            'column_filter' => array (
                'logical_operator' => LogicalOperatorConst::CONST_AND,
                'sub_filters' => array (
                    array (
                        'column_name' => 'attr1',
                        'value' => 10,
                        'comparator' => ComparatorTypeConst::CONST_GREATER_THAN
                    ),
                    array (
                        'column_name' => 'attr2',
                        'value' => 'att10001',
                        'comparator' => ComparatorTypeConst::CONST_LESS_THAN
                    )
                )
            )
        );
        $getRangeRes = $this->otsClient->getRange ($getRange);
        $this->assertNotEmpty ($getRangeRes['next_start_primary_key']);
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件下以及多重逻辑判读条件下，使用GetRange扫描数据是否成功。
     */
    public function testGetGetRangeWithMultipleCompositeCondition() {
        for($i = 1; $i < 10001; $i ++) {
            $putdata = array (
                'table_name' => self::$usedTables[2],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array (
                    array('PK1', $i)
                ),
                'attribute_columns' => array (
                    array('att1', $i),
                    array('att2', 'att' . $i)
                )
            );
            $this->otsClient->putRow ($putdata);
        }
        
        $getRange = array (
            'table_name' => self::$usedTables[2],
            'direction' => DirectionConst::CONST_FORWARD,
            'columns_to_get' => array (
                'att1',
                'att2'
            ),
            'limit' => 5000,
            'max_versions' => 1,
            'inclusive_start_primary_key' => array (
                array('PK1', 1)
            ),
            'exclusive_end_primary_key' => array (
                array('PK1', null, PrimaryKeyTypeConst::CONST_INF_MAX)
            ),
            'column_filter' => array (
                'logical_operator' => LogicalOperatorConst::CONST_OR,
                'sub_filters' => array (
                    array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr1',
                                'value' => 10,
                                'comparator' => ComparatorTypeConst::CONST_GREATER_THAN
                            ),
                            array (
                                'column_name' => 'attr1',
                                'value' => 20000,
                                'comparator' => ComparatorTypeConst::CONST_LESS_THAN
                            )
                        )
                    ),
                    array (
                        'logical_operator' => LogicalOperatorConst::CONST_AND,
                        'sub_filters' => array (
                            array (
                                'column_name' => 'attr2',
                                'value' => 'att1001',
                                'comparator' => ComparatorTypeConst::CONST_GREATER_THAN
                            ),
                            array (
                                'column_name' => 'attr2',
                                'value' => 'att1002',
                                'comparator' => ComparatorTypeConst::CONST_LESS_EQUAL
                            )
                        )
                    )
                )
            )
        );
        $getRangeRes = $this->otsClient->getRange ($getRange);
        $this->assertNotEmpty ($getRangeRes['next_start_primary_key']);
    }
}

