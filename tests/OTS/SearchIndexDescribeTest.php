<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\SortOrderConst;
use Aliyun\OTS\Consts\SortModeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class SearchIndexDescribeTest extends SDKTestBase {

    private static $tableName = 'testSearchTableName';
    private static $indexName = 'testSearchIndexName';

    public function setup()
    {

        $createTableRequest = array (
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
        SDKTestBase::createInitialTable($createTableRequest);

        $createIndexRequest = array(
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
                    ),
                    array(
                        'field_name' => 'text',
                        'field_type' => FieldTypeConst::TEXT,
                        'analyzer' => 'single_word',
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'geo',
                        'field_type' => FieldTypeConst::GEO_POINT,
                        'index' => true,
                        'index_options' => 'DOCS',
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'long',
                        'field_type' => FieldTypeConst::LONG,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'double',
                        'field_type' => FieldTypeConst::DOUBLE,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'boolean',
                        'field_type' => FieldTypeConst::BOOLEAN,
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'array',
                        'field_type' => FieldTypeConst::KEYWORD,
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => true
                    )
                ),
                'index_setting' => array(
                    'routing_fields' => array("PK0")
                ),
                "index_sort" => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC,
                            'mode' => SortModeConst::SORT_MODE_MIN,
                        )
                    ),
                    array(
                        'pk_sort' => array(
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($createIndexRequest);
    }

    public function tearDown()
    {
        SDKTestBase::cleanUpSearchIndex(self::$tableName);
        SDKTestBase::cleanUp(array(self::$tableName));
    }

    public function testCreateSearchIndexKeyword()
    {

        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName
        ));

        $this->assertEquals(count($response['index_schema']['field_schemas']), 7);
        $this->assertEquals(count($response['index_schema']['index_setting']['routing_fields']), 1);
        $this->assertEquals($response['index_schema']['index_setting']['routing_fields'][0], 'PK0');
        $this->assertEquals(count($response['index_schema']['index_sort']), 2);
        $this->assertNotEmpty($response['index_schema']['index_sort'][0]['field_sort']);

        $fieldSort = $response['index_schema']['index_sort'][0]['field_sort'];
        $this->assertEquals($fieldSort['field_name'], 'keyword');
        $this->assertEquals($fieldSort['order'], SortOrderConst::SORT_ORDER_ASC);
        $this->assertEquals($fieldSort['mode'], SortModeConst::SORT_MODE_MIN);

        $this->assertNotEmpty($response['index_schema']['index_sort'][1]['pk_sort']);
        $sortSort = $response['index_schema']['index_sort'][1]['pk_sort'];
        $this->assertEquals($sortSort['order'], SortOrderConst::SORT_ORDER_ASC);


    }
}

