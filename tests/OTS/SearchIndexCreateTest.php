<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class SearchIndexCreateTest extends SDKTestBase {

    private static $tableName = 'testSearchTableName';

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

    public function testCreateSearchIndexKeyword()
    {
        $indexName = 'testKeyword';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
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
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'keyword');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::KEYWORD);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
    }

    public function testCreateSearchIndexText()
    {
        $indexName = 'testText';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'text',
                        'field_type' => FieldTypeConst::TEXT,
                        'analyzer' => 'single_word',
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                ),
                'index_setting' => array()
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'text');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::TEXT);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['analyzer'], 'single_word');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
    }

    public function testCreateSearchIndexGeo()
    {
        $indexName = 'testGeo';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'geo',
                        'field_type' => FieldTypeConst::GEO_POINT,
                        'index' => true,
                        'index_options' => OTS\Consts\IndexOptionsConst::DOCS,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'geo');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::GEO_POINT);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index_options'], OTS\Consts\IndexOptionsConst::DOCS);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
    }

    public function testCreateSearchIndexLong()
    {
        $indexName = 'testLong';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'long',
                        'field_type' => FieldTypeConst::LONG,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'long');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::LONG);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
    }

    public function testCreateSearchIndexDouble()
    {
        $indexName = 'testDouble';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'double',
                        'field_type' => FieldTypeConst::DOUBLE,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'double');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::DOUBLE);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
    }

    public function testCreateSearchIndexBoolean()
    {
        $indexName = 'testBoolean';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'boolean',
                        'field_type' => FieldTypeConst::BOOLEAN,
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'boolean');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::BOOLEAN);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
    }

    public function testCreateSearchIndexArray()
    {
        $indexName = 'testArray';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'array',
                        'field_type' => FieldTypeConst::KEYWORD,
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => true
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'array');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::KEYWORD);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], true);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], true);
    }

    public function testCreateSearchIndexNested()
    {
        $indexName = 'testNested';
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => $indexName,
            'schema' => array(
                'field_schemas' => array(
                    array(
                        'field_name' => 'nested',
                        'field_type' => FieldTypeConst::NESTED,
                        'index' => false,
                        'enable_sort_and_agg' => false,
                        'store' => false,
                        'field_schemas' => array(
                            array(
                                'field_name' => 'nested_keyword',
                                'field_type' => FieldTypeConst::KEYWORD,
                                'index' => true,
                                'enable_sort_and_agg' => true,
                                'store' => true,
                                'is_array' => false
                            )
                        )
                    ),
                )
            )
        );

        SDKTestBase::createSearchIndex($request);
        $response = $this->otsClient->describeSearchIndex(array(
            'table_name' => self::$tableName,
            'index_name' => $indexName
        ));
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_name'], 'nested');
        $this->assertEquals($response['index_schema']['field_schemas'][0]['field_type'], FieldTypeConst::NESTED);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['index'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['enable_sort_and_agg'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['store'], false);
        $this->assertEquals($response['index_schema']['field_schemas'][0]['is_array'], false);
        $this->assertNotEmpty($response['index_schema']['field_schemas'][0]['field_schemas'][0]);

        $subFieldSchema = $response['index_schema']['field_schemas'][0]['field_schemas'][0];
        $this->assertEquals($subFieldSchema['field_name'], 'nested_keyword');
        $this->assertEquals($subFieldSchema['field_type'], FieldTypeConst::KEYWORD);
        $this->assertEquals($subFieldSchema['index'], true);
        $this->assertEquals($subFieldSchema['enable_sort_and_agg'], true);
        $this->assertEquals($subFieldSchema['store'], true);
        $this->assertEquals($subFieldSchema['is_array'], false);

    }
}

