<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


class DescribeTableTest extends SDKTestBase {

    private static $usedTables = array (
        'test5',
        'test'
    );

    public function setup() {
       $this->cleanUp (self::$usedTables);
    }
    
    /*
     * IntegerPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 INTEGER 的情况。
     */
    public function testIntegerPKInSchema() {

        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
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
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
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
                'table_name' => self::$usedTables[0],
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
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
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

    public function tearDown()
    {
        $table_name = self::$usedTables;
        for($i = 0; $i < count ($table_name); $i ++) {
            $request = array (
                'table_name' => $table_name[$i]
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

