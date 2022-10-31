<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;


require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class CreateTableTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable',
        'test1',
        'test2',
        'test3',
        'test4',
        'test',
        'test5',
        'testAllowUpdateTrue',
        'testAllowUpdateFalse'
    );

    public function setup() {
        $table_name = self::$usedTables;
        for($i = 0; $i < count ($table_name); $i ++) {
            $request = array (
                    'table_name' => $table_name[$i]
            );
            try {
                $this->otsClient->deleteTable ($request);
                SDKTestBase::waitForAvoidFrequency();
            } catch (OTS\OTSServerException $exc) {
                if ($exc->getOTSErrorCode() == 'OTSObjectNotExist')
                    continue;
            }
        }
    }

    public function tearDown() {
        $table_name = self::$usedTables;
        for($i = 0; $i < count ($table_name); $i ++) {
            $request = array (
                'table_name' => $table_name[$i]
            );
            try {
                $this->otsClient->deleteTable ($request);
                SDKTestBase::waitForAvoidFrequency();
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
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                ),
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
        );
        $this->otsClient->createTable ($tablebody);
        SDKTestBase::waitForAvoidFrequency();
        $tablename = array (
            self::$usedTables[0]
        );
        // $tablename['mytable'] = 111;
        $this->assertEquals ($this->otsClient->listTable (array ()), $tablename);
        // $this->assertContains();
        $table_name['table_name'] = self::$usedTables[0];
        $teturn = array (
            'table_name' => self::$usedTables[0],
            'primary_key_schema' => array (
                array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
            ),
            'defined_column' => array()
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
            'table_meta' => array (
                'table_name' => '',
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            SDKTestBase::waitForAvoidFrequency();
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
            'table_meta' => array (
                'table_name' => 'testU+0053',
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            SDKTestBase::waitForAvoidFrequency();
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
    public function testTableName1KB() { // 1kb is too long for monitor data pk
        $name = '';
        for($i = 1; $i < 300; $i ++) {
            $name .= 'a';
        }
        $tablebody = array (
            'table_meta' => array (
                'table_name' => $name,
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            SDKTestBase::waitForAvoidFrequency();
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
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[1],
                'primary_key_schema' => array ()
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            SDKTestBase::waitForAvoidFrequency();
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'The number of primary key columns must be in range: [1, 4].'; // TODO make right expect
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * OnePKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含1个PK时的情况
     */
    public function testOnePKInSchema() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[2],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            'table_name' => $tablebody['table_meta']['table_name'],
            'primary_key_schema' => array (
                array('PK1', PrimaryKeyTypeConst::CONST_STRING)
            ),
            'defined_column' => array()
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * FourPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含4个PK时的情况
     */
    public function testFourPKInSchema() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[3],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            'table_name' => $tablebody['table_meta']['table_name'],
            'primary_key_schema' => array (
                array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
            ),
            'defined_column' => array()
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * TooMuchPKInSchema
     * 测试TableMeta包含1000个PK的情况，CreateTable期望返回错误消息：The number of primary key columns must be in range: [1, 4].
     */
    public function testTooMuchPKInSchema() {
        $key = array ();
        for($i = 1; $i < 1001; $i ++) {
            $key[] = array('a' . $i,  PrimaryKeyTypeConst::CONST_INTEGER);
        }
        // print_r($key);die;
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[4],
                'primary_key_schema' => $key
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        try {
            $this->otsClient->createTable ($tablebody);
            SDKTestBase::waitForAvoidFrequency();
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = 'The number of primary key columns must be in range: [1, 4].';
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     * IntegerPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 INTEGER 的情况。
     */
    public function testIntegerPKInSchema() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[5],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            'table_name' => $tablebody['table_meta']['table_name'],
            'primary_key_schema' => array (
                array('PK1', PrimaryKeyTypeConst::CONST_INTEGER),
                array('PK2', PrimaryKeyTypeConst::CONST_INTEGER)
            ),
            'defined_column' => array()
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }
    
    /*
     * StringPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 STRING 的情况。
     */
    public function testStringPKInSchema() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[5],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
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
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            'table_name' => $tablebody['table_meta']['table_name'],
            'primary_key_schema' => array (
                array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                array('PK2', PrimaryKeyTypeConst::CONST_STRING)
            ),
            'defined_column' => array()
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($teturn, $table_meta['table_meta']);
    }

    /*
     * 测试CreateTable时主动允许更新，默认True
     */
    public function testAllowUpdateDefaultTrue() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[7],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 1,
                'deviation_cell_version_in_sec' => 86400,
//                'allow_update' => true  // 默认不指定：true
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertTrue($table_meta['table_options']['allow_update']);

        try {
            $putRow = array(
                'table_name' => self::$usedTables[7],
                'primary_key' => array ( // 主键
                    array('PK1', '123'),
                    array('PK2', 'abc')
                ),
                'attribute_columns' => array( // 属性
                    array('attr0', 456), // INTEGER类型
                    array('attr1', 'Hangzhou'), // STRING类型
                    array('attr2', 3.14), // DOUBLE类型
                    array('attr3', true), // BOOLEAN类型
                    array('attr4', false), // BOOLEAN类型
                )
            );
            $this->otsClient->putRow($putRow);
        } catch (OTS\OTSServerException $exec) {
            $this->fail('PutRow should succeed', $exec);
        }

        try {
            $updateRow = array(
                'table_name' => self::$usedTables[7],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array ( // 主键
                    array('PK1', '123'),
                    array('PK2', 'abc')
                ),
                'update_of_attribute_columns' => array( // 属性
                    'PUT' => array(
                        array('attr5', 456), // INTEGER类型
                    ),
                )
            );
            $this->otsClient->updateRow($updateRow);
        } catch (OTS\OTSServerException $exec) {
            $this->fail('UpdateRow should succeed', $exec);
        }
    }

    /*
     * 测试CreateTable时主动指定禁止更新，数据写入报错
     */
    public function testAllowUpdateFalse() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[8],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 1,
                'deviation_cell_version_in_sec' => 86400,
                'allow_update' => false
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertFalse($table_meta['table_options']['allow_update']);

        try {
            $putRow = array(
                'table_name' => self::$usedTables[8],
                'primary_key' => array ( // 主键
                    array('PK1', '123'),
                    array('PK2', 'abc')
                ),
                'attribute_columns' => array( // 属性
                    array('attr0', 456), // INTEGER类型
                    array('attr1', 'Hangzhou'), // STRING类型
                    array('attr2', 3.14), // DOUBLE类型
                    array('attr3', true), // BOOLEAN类型
                    array('attr4', false), // BOOLEAN类型
                )
            );
            $this->otsClient->putRow($putRow);
        } catch (OTS\OTSServerException $exec) {
            $this->fail('PutRow should succeed', $exec);
        }

        try {
            $updateRow = array(
                'table_name' => self::$usedTables[8],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array ( // 主键
                    array('PK1', '123'),
                    array('PK2', 'abc')
                ),
                'update_of_attribute_columns' => array( // 属性
                    'PUT' => array(
                        array('attr5', 456), // INTEGER类型
                    ),
                )
            );
            $this->otsClient->updateRow($updateRow);
            $this->fail('UpdateRow should failed');
        } catch (OTS\OTSServerException $exec) {
            print $exec;
            $this->assertEquals($exec->getOTSErrorCode(), "OTSParameterInvalid");
            $this->assertEquals($exec->getOTSErrorMessage(), "Update operation is not allowed in this table");
        }
    }
}
