<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class DeleteTableTest extends SDKTestBase {

    private static $usedTables = array (
        'myTable'
    );
    
    /*
     *
     * DeleteTable
     * 创建一个表，并删除，ListTable期望返回0个TableName。
     */
    public function testDeleteTable() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
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
        $this->otsClient->createTable ($tablebody);
        SDKTestBase::waitForAvoidFrequency();

        $request = array (
            'table_name' => self::$usedTables[0]
        );
        // print_r($this->listtable->ListTable());
        $response = $this->otsClient->deleteTable ($request);
        $this->assertEquals ($response, array ());
        $this->assertEmpty ($this->otsClient->listTable (array ()));
    }
    
    /*
     *
     * DeleteTableEmpty
     * 指定表名为空，抛出对应错误信息 Invalid table name: ''.
     */
    public function testDeleteTableEmpty() {
        $request = array (
            'table_name' => ''
        );
        
        try {
            $this->otsClient->deleteTable ($request);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
    
    /*
     *
     * DeleteTableEmpty
     * 指定不存在的表，抛出对应错误信息 Requested table does not exist
     */
    public function testNotExiteTableName() {
        $request = array (
            'table_name' => 'TableThatNotExisting'
        );
        
        try {
            $this->otsClient->deleteTable ($request);
            $this->fail ('An expected exception has not been raised.');
        } catch (\Aliyun\OTS\OTSServerException $exc) {
            $c = "Requested table does not exist.";
            $this->assertEquals ($c, $exc->getOTSErrorMessage ());
        }
    }
}

