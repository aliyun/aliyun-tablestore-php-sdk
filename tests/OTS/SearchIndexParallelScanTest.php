<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ColumnReturnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\QueryOperatorConst;
use Aliyun\OTS\Consts\QueryTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\ScoreModeConst;
use Aliyun\OTS\Consts\SortModeConst;
use Aliyun\OTS\Consts\SortOrderConst;
use Aliyun\OTS\Consts\GeoDistanceTypeConst;
use Aliyun\OTS\Consts\GroupByTypeConst;
use Aliyun\OTS\Consts\AggregationTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class SearchIndexParallelScanTest extends SDKTestBase {

    private static $tableName = 'testSearchTableName';
    private static $indexName = 'testSearchIndexName';

    public static function setUpBeforeClass()
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

        self::createIndex();
        self::insertData();
        self::waitForSearchIndexSync();
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUpSearchIndex(self::$tableName);
        SDKTestBase::cleanUp(array(self::$tableName));
    }

    public function testParallelScan() {
        $computeSplitsPointReq = array(
            'table_name' => self::$tableName,
            'search_index_splits_options' => array(
                'index_name' => self::$indexName
            )
        );
        $computeSplits = $this->otsClient->computeSplits($computeSplitsPointReq);
        $this->assertEquals($computeSplits["splits_size"], 1);
        $this->assertNotNull($computeSplits["session_id"]);
        print json_encode ($computeSplits, JSON_PRETTY_PRINT);

        $totalCount = 0;
        $scanQuery = array(
            'query' => array(
                'query_type' => QueryTypeConst::MATCH_ALL_QUERY
            ),
            'limit' => 2,
            'alive_time' => 30,
            'token' => null,
            'current_parallel_id' => 0,
            'max_parallel' => 1
        );
        $parallelScanReq = array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL_FROM_INDEX, // RETURN_ALL is not allow in parallel_scan, use RETURN_ALL_FROM_INDEX
                'return_names' => array('geo', 'text', 'long', 'keyword')
            ),
            'session_id' => $computeSplits['session_id'],
            'scan_query' => $scanQuery
        );

        $parallelScanRes = $this->otsClient->parallelScan($parallelScanReq);

        $totalCount += count($parallelScanRes['rows']);

        while (!is_null($parallelScanRes['next_token'])) {
            $parallelScanReq['scan_query']['token'] = $parallelScanRes['next_token'];
            $parallelScanRes = $this->otsClient->parallelScan($parallelScanReq);
            print json_encode ($parallelScanRes['rows'], JSON_PRETTY_PRINT);

            $totalCount += count($parallelScanRes['rows']);
        }
        $this->assertEquals($totalCount, 100);
        print "TotalCount: " . $totalCount;
    }

    public static function createIndex() {
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
                        'enable_sort_and_agg' => true,
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
                    ),
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
                            ),
                            array(
                                'field_name' => 'nested_long',
                                'field_type' => FieldTypeConst::LONG,
                                'index' => true,
                                'enable_sort_and_agg' => true,
                                'store' => true,
                                'is_array' => false
                            ),
                        )
                    ),
                ),
                'index_setting' => array(
                    'routing_fields' => array("PK0")
                )
            )
        );

        SDKTestBase::createSearchIndex($createIndexRequest);
    }

    private static function insertData() {
        for ($i = 0; $i < 100; $i++) {
            $request = array(
                'table_name' => self::$tableName,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array ( // 主键
                    array('PK0', $i),
                    array('PK1', 'search')
                ),
                'attribute_columns' => array(
                    array('keyword', 'keyword'),
                    array('text', 'ots php search index' . $i),
                    array('geo', '5.' . $i . ',6.' . $i),
                    array('long', $i),
                    array('double', $i + 0.1),
                    array('boolean', $i % 3 == 0),
                    array('array', '["search","index' . $i . '"]'),
                    array('nested', '[{"nested_keyword":"sub","nested_long":' . $i . '}]')
                )
            );

            SDKTestBase::putInitialData($request);
        }
    }

    public static function getBaseRequest()
    {
        return array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 0,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_ALL_QUERY
                ),
                'group_bys' => array(
                    'group_bys' => array(
                        array(
                            'name' => 'group_by_GROUP_BY_FIELD',
                            'type' => GroupByTypeConst::GROUP_BY_FIELD,
                            'body' => array(
                                'field_name' => 'boolean',
                                'size' => 3,
                                'min_doc_count' => 0,
                                'sort' => array(
                                    'sorters' => array(
                                        array(
                                            'group_key_sort' => array(
                                                'order' => SortOrderConst::SORT_ORDER_DESC
                                            ),
                                        ),
                                    )
                                ),
                            )
                        ),
                    ),
                ),
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL_FROM_INDEX,
            ),
        );
    }
}

