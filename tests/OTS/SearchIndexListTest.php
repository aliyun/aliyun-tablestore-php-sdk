<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class SearchIndexListTest extends SDKTestBase {

    private static $tableName = 'testSearchTableName';
    private static $indexName = 'testSearchIndexName';

    public function setup()
    {

        $request = array (
            'table_meta' => array (
                'table_name' => self::$tableName, // 表名为 MyTable
                'primary_key_schema' => array (
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
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
        SDKTestBase::createInitialTable($request);
    }

    public function tearDown()
    {
        SDKTestBase::cleanUpSearchIndex(self::$tableName);
        SDKTestBase::cleanUp(array(self::$tableName));
    }

    /*
     * ListSearchIndex0
     * 在没有表的情况下 ListSearchIndex，期望返回0个Table Name
     */
    public function testListSearchIndex0() {
        $this->assertEmpty ($this->otsClient->listSearchIndex(array (
            'table_name' => self::$tableName
        )));
    }

    /*
     * ListSearchIndex1
     * 在没有表的情况下 ListSearchIndex，期望返回1个索引: 'testSearchIndexName'
     */
    public function testListSearchIndex1()
    {
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'keyword',
                        'field_type' => FieldTypeConst::KEYWORD,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    )
                ),
                'index_setting' => array(
                    'routing_fields' => array("PK1")
                ),
            )
        );

        SDKTestBase::createSearchIndex($request);
        $searchIndexList = $this->otsClient->listSearchIndex(array(
            'table_name' => self::$tableName
        ));
        $this->assertEquals($searchIndexList[0]['index_name'], self::$indexName);
    }
}

