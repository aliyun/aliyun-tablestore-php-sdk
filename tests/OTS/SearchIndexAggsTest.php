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

class SearchIndexAggsTest extends SDKTestBase {

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

    public function testAggAverage() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_avg',
                    'type' => AggregationTypeConst::AGG_AVG,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_avg");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_AVG);
        $this->assertEquals($agg_results[0]["agg_result"]["value"] , 49.5);
    }

    public function testAggMax() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_max',
                    'type' => AggregationTypeConst::AGG_MAX,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_max");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_MAX);
        $this->assertEquals($agg_results[0]["agg_result"]["value"] , 99);
    }

    public function testAggMin() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_min',
                    'type' => AggregationTypeConst::AGG_MIN,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_min");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_MIN);
        $this->assertEquals($agg_results[0]["agg_result"]["value"] , 0);
    }

    public function testAggSum() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_sum',
                    'type' => AggregationTypeConst::AGG_SUM,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_sum");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_SUM);
        $this->assertEquals($agg_results[0]["agg_result"]["value"] , 4950);
    }

    public function testAggCount() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_count',
                    'type' => AggregationTypeConst::AGG_COUNT,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_count");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_COUNT);
        $this->assertEquals($agg_results[0]["agg_result"]["value"] , 100);
    }

    public function testAggDistinctCount() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_distinct_count',
                    'type' => AggregationTypeConst::AGG_DISTINCT_COUNT,
                    'body' => array(
                        'field_name' => 'boolean',
                        'missing' => false
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_distinct_count");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_DISTINCT_COUNT);
        $this->assertEquals($agg_results[0]["agg_result"]["value"] , 2);
    }

    public function testAggTopRows() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'avg_top_rows',
                    'type' => AggregationTypeConst::AGG_TOP_ROWS,
                    'body' => array(
                        'limit' => 2,
                        'sort' => array(
                            'sorters' => array(
                                array(
                                    'field_sort' => array(
                                        'field_name' => 'long',
                                        'order' => SortOrderConst::SORT_ORDER_DESC
                                    )
                                )
                            )
                        )
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);

        $this->assertEquals($agg_results[0]["name"], "avg_top_rows");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_TOP_ROWS);

        $rows = $agg_results[0]["agg_result"]["rows"];
        $row1 = $rows[0];
        $this->assertEquals(count($row1["primary_key"]), 2);
        $this->assertEquals($row1["primary_key"][0][0], "PK0");
        $this->assertEquals($row1["primary_key"][0][1], 99);
        $this->assertEquals($row1["primary_key"][1][0], "PK1");
        $this->assertEquals($row1["primary_key"][1][1], "search");

        $row2 = $rows[1];
        $this->assertEquals(count($row2["primary_key"]), 2);
        $this->assertEquals($row2["primary_key"][0][0], "PK0");
        $this->assertEquals($row2["primary_key"][0][1], 98);
        $this->assertEquals($row2["primary_key"][1][0], "PK1");
        $this->assertEquals($row2["primary_key"][1][1], "search");
    }

    public function testAggPercentiles() {
        $request = $this->getBaseRequest();
        $request["search_query"]["aggs"] = array(
            'aggs' => array(
                array(
                    'name' => 'agg_percentiles',
                    'type' => AggregationTypeConst::AGG_PERCENTILES,
                    'body' => array(
                        'field_name' => 'long',
                        'percentiles' => array(60, 80, 100),
                        'missing' => 0
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $agg_results = $response["aggs"]["agg_results"];

        print json_encode($agg_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($agg_results), 1);
        $this->assertEquals($agg_results[0]["name"], "agg_percentiles");
        $this->assertEquals($agg_results[0]["type"], AggregationTypeConst::AGG_PERCENTILES);
        $this->assertEquals(count($agg_results[0]["agg_result"]["items"]) , 3);

        $item0 = $agg_results[0]["agg_result"]["items"][0];
        $this->assertEquals($item0["key"], 60.);
        $this->assertEquals($item0["value"], 59);
        $item1 = $agg_results[0]["agg_result"]["items"][1];
        $this->assertEquals($item1["key"], 80.);
        $this->assertEquals($item1["value"], 79);
        $item2 = $agg_results[0]["agg_result"]["items"][2];
        $this->assertEquals($item2["key"], 100.);
        $this->assertEquals($item2["value"], 99);
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

