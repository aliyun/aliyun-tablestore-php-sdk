<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\DefinedColumnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class GlobalIndexCreate2Test extends SDKTestBase {

    private static $tableName = 'testGlobalTableName';
    private static $indexName1 = 'testGlobalTableWithIndex1';
    private static $indexName2 = 'testGlobalTableWithIndex2';
    public function setup()
    {

    }

    public function testCreateTableWithGlobalIndex() {
        $request = array (
            'table_meta' => array (
                'table_name' => self::$tableName, // 表名为 testGlobalTableName
                'primary_key_schema' => array (
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
                ),
                'defined_column' => array(
                    array('col1', DefinedColumnTypeConst::DCT_STRING),
                    array('col2', DefinedColumnTypeConst::DCT_INTEGER)
                )
            ),

            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
                'max_versions' => 1,    // 最大数据版本
                'deviation_cell_version_in_sec' => 86400  // 数据有效版本偏差，单位秒
            )
        );
        $this->otsClient->createTable($request);

        $indexes = array(
            array(
                'name' => self::$indexName1,
                'primary_key' => array('col1'),
                'defined_column' => array('col2')
            ),
            array(
                'name' => self::$indexName2,
                'primary_key' => array('PK1'),
                'defined_column' => array('col1', 'col2')
            )
        );
        $this->otsClient->createIndex(array('table_name' => self::$tableName, 'index_meta' => $indexes[0]));
        $this->otsClient->createIndex(array('table_name' => self::$tableName, 'index_meta' => $indexes[1]));

        $response = $this->otsClient->describeTable(array('table_name' => self::$tableName));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertEquals($response['table_meta']['defined_column'][0][0], 'col1');
        $this->assertEquals($response['table_meta']['defined_column'][0][1], 'STRING');
        $this->assertEquals($response['table_meta']['defined_column'][1][0], 'col2');
        $this->assertEquals($response['table_meta']['defined_column'][1][1], 'INTEGER');

        $this->assertEquals(count($response['index_metas']), 2);
        $this->assertEquals($response['index_metas'][0]['name'], self::$indexName1);
        $this->assertEquals($response['index_metas'][1]['name'], self::$indexName2);

        $this->otsClient->dropIndex(array('table_name' => self::$tableName, 'index_name' => self::$indexName1));
        $this->otsClient->dropIndex(array('table_name' => self::$tableName, 'index_name' => self::$indexName2));
        $this->otsClient->deleteTable(array('table_name' => self::$tableName));
    }

    public function tearDown()
    {
        try {
            $this->otsClient->dropIndex(array('table_name' => self::$tableName, 'index_name' => self::$indexName1));
        } catch (\Exception $ex) {}
        try {
            $this->otsClient->dropIndex(array('table_name' => self::$tableName, 'index_name' => self::$indexName2));
        } catch (\Exception $ex) {}
        try {
            $this->otsClient->dropIndex(array('table_name' => self::$tableName, 'index_name' => self::$indexName1));
        } catch (\Exception $ex) {}
    }


}

