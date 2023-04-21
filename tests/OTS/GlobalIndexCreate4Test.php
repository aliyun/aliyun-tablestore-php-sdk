<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\DefinedColumnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\IndexUpdateModeConst;
use Aliyun\OTS\Consts\IndexTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class GlobalIndexCreate4Test extends SDKTestBase {

    private static $tableName = 'testGlobalTableName';
    private static $indexName1 = 'testIndexLocalIndex';
    private static $indexName2 = 'testIndexGlobalIndex';
    private static $indexName3 = 'testIndexDefaultIndex';
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
            ),
            'index_metas' => array(
                array(
                    'name' => self::$indexName1,
                    'primary_key' => array('PK0'),
                    'defined_column' => array('col2'),
                    'index_type' => IndexTypeConst::LOCAL_INDEX,
                    'index_update_mode' => IndexUpdateModeConst::SYNC_INDEX
                ),
                array(
                    'name' => self::$indexName2,
                    'primary_key' => array('PK1'),
                    'defined_column' => array('col1', 'col2'),
                    'index_type' => IndexTypeConst::GLOBAL_INDEX,
                    'index_update_mode' => IndexUpdateModeConst::ASYNC_INDEX
                ),
                array(
                    'name' => self::$indexName3,
                    'primary_key' => array('PK0'),
                    'defined_column' => array('col1', 'col2'),
                )
            )
        );
        $this->otsClient->createTable($request);

        $response = $this->otsClient->describeTable(array('table_name' => self::$tableName));

        $this->assertEquals(count($response['index_metas']), 3);
        $this->assertEquals($response['index_metas'][0]['name'], self::$indexName1);
        $this->assertEquals($response['index_metas'][0]['index_type'], IndexTypeConst::LOCAL_INDEX);
        $this->assertEquals($response['index_metas'][0]['index_update_mode'], IndexUpdateModeConst::SYNC_INDEX);
        $this->assertEquals($response['index_metas'][1]['name'], self::$indexName2);
        $this->assertEquals($response['index_metas'][1]['index_type'], IndexTypeConst::GLOBAL_INDEX);
        $this->assertEquals($response['index_metas'][1]['index_update_mode'], IndexUpdateModeConst::ASYNC_INDEX);
        $this->assertEquals($response['index_metas'][2]['name'], self::$indexName3);
        $this->assertEquals($response['index_metas'][2]['index_type'], IndexTypeConst::GLOBAL_INDEX);
        $this->assertEquals($response['index_metas'][2]['index_update_mode'], IndexUpdateModeConst::ASYNC_INDEX);
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
            $this->otsClient->dropIndex(array('table_name' => self::$tableName, 'index_name' => self::$indexName3));
        } catch (\Exception $ex) {}
        try {
            $this->otsClient->deleteTable(array('table_name' => self::$tableName));
        } catch (\Exception $ex) {}
    }


}

