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

class SearchIndexGroupBysTest extends SDKTestBase {

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

    public function testGroupByField() {
        $request = $this->getBaseRequest();
        $request["search_query"]["group_bys"] = array(
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
        );
        $response = $this->otsClient->search($request);
        $group_by_results = $response["group_bys"]["group_by_results"];

        print json_encode($group_by_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($group_by_results), 1);
        $this->assertEquals($group_by_results[0]["name"], "group_by_GROUP_BY_FIELD");
        $this->assertEquals($group_by_results[0]["type"], GroupByTypeConst::GROUP_BY_FIELD);
        $this->assertEquals(count($group_by_results[0]["group_by_result"]["items"]) , 2);
        $item0 = $group_by_results[0]["group_by_result"]["items"][0];
        $this->assertEquals($item0["key"], "true");
        $this->assertEquals($item0["row_count"], 34);
        $item1 = $group_by_results[0]["group_by_result"]["items"][1];
        $this->assertEquals($item1["key"], "false");
        $this->assertEquals($item1["row_count"], 66);
    }

    public function testGroupByRange() {
        $request = $this->getBaseRequest();
        $request["search_query"]["group_bys"] = array(
            'group_bys' => array(
                array(
                    'name' => 'group_by_GROUP_BY_RANGE',
                    'type' => GroupByTypeConst::GROUP_BY_RANGE,
                    'body' => array(
                        'field_name' => 'long',
                        'ranges' => array(
                            array(
                                'from' => 1,
                                'to' => 3
                            ),
                            array(
                                'from' => 3,
                                'to' => 6
                            ),
                            array(
                                'from' => 6,
                                'to' => 10
                            )
                        )
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $group_by_results = $response["group_bys"]["group_by_results"];

        print json_encode($group_by_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($group_by_results), 1);
        $this->assertEquals($group_by_results[0]["name"], "group_by_GROUP_BY_RANGE");
        $this->assertEquals($group_by_results[0]["type"], GroupByTypeConst::GROUP_BY_RANGE);
        $this->assertEquals(count($group_by_results[0]["group_by_result"]["items"]) , 3);
        $item0 = $group_by_results[0]["group_by_result"]["items"][0];
        $this->assertEquals($item0["row_count"], 2);
        $item1 = $group_by_results[0]["group_by_result"]["items"][1];
        $this->assertEquals($item1["row_count"], 3);
        $item2 = $group_by_results[0]["group_by_result"]["items"][2];
        $this->assertEquals($item2["row_count"], 4);
    }

    public function testGroupByFilter() {
        $request = $this->getBaseRequest();
        $request["search_query"]["group_bys"] = array(
            'group_bys' => array(
                array(
                    'name' => 'group_by_GROUP_BY_FILTER',
                    'type' => GroupByTypeConst::GROUP_BY_FILTER,
                    'body' => array(
                        'filters' => array(
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => false
                                )
                            ),
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => true
                                )
                            )
                        )
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $group_by_results = $response["group_bys"]["group_by_results"];

        print json_encode($group_by_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($group_by_results), 1);
        $this->assertEquals($group_by_results[0]["name"], "group_by_GROUP_BY_FILTER");
        $this->assertEquals($group_by_results[0]["type"], GroupByTypeConst::GROUP_BY_FILTER);
        $this->assertEquals(count($group_by_results[0]["group_by_result"]["items"]) , 2);
        $item0 = $group_by_results[0]["group_by_result"]["items"][0];
        $this->assertEquals($item0["row_count"], 66);
        $item1 = $group_by_results[0]["group_by_result"]["items"][1];
        $this->assertEquals($item1["row_count"], 34);
    }

    public function testGroupByGeoDistance() {
        $request = $this->getBaseRequest();
        $request["search_query"]["group_bys"] = array(
            'group_bys' => array(
                array(
                    'name' => 'group_by_GROUP_BY_GEO_DISTANCE',
                    'type' => GroupByTypeConst::GROUP_BY_GEO_DISTANCE,
                    'body' => array(
                        'field_name' => 'geo',
                        'origin' => array(
                            'lat' => 5,
                            'lon' => 6
                        ),
                        'ranges' => array(
                            array(
                                'from' => 0.,
                                'to' => 1000.
                            ),
                            array(
                                'from' => 10000.,
                                'to' => 100000.
                            ),
                            array(
                                'from' => 100000.,
                            ),
                        )
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $group_by_results = $response["group_bys"]["group_by_results"];

        print json_encode($group_by_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($group_by_results), 1);
        $this->assertEquals($group_by_results[0]["name"], "group_by_GROUP_BY_GEO_DISTANCE");
        $this->assertEquals($group_by_results[0]["type"], GroupByTypeConst::GROUP_BY_GEO_DISTANCE);
        $this->assertEquals(count($group_by_results[0]["group_by_result"]["items"]) , 3);
        $item0 = $group_by_results[0]["group_by_result"]["items"][0];
        $this->assertEquals($item0["row_count"], 1);
        $item1 = $group_by_results[0]["group_by_result"]["items"][1];
        $this->assertEquals($item1["row_count"], 60);
        $item2 = $group_by_results[0]["group_by_result"]["items"][2];
        $this->assertEquals($item2["row_count"], 39);
    }

    public function testGroupByHistogram() {
        $request = $this->getBaseRequest();
        $request["search_query"]["group_bys"] = array(
            'group_bys' => array(
                array(
                    'name' => 'group_by_GROUP_BY_HISTOGRAM',
                    'type' => GroupByTypeConst::GROUP_BY_HISTOGRAM,
                    'body' => array(
                        'field_name' => 'long',
                        'interval' => 3,
                        'missing' => 0,
                        'min_doc_count' => 0,
                        'field_range' => array(
                            'min' => 2,
                            'max' => 10,
                        ),
                        'sort' => array(
                            'sorters' => array(
                                array(
                                    'row_count_sort' => array(
                                        'order' => SortOrderConst::SORT_ORDER_ASC
                                    )
                                )
                            )
                        )
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $group_by_results = $response["group_bys"]["group_by_results"];

        print json_encode($group_by_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($group_by_results), 1);
        $this->assertEquals($group_by_results[0]["name"], "group_by_GROUP_BY_HISTOGRAM");
        $this->assertEquals($group_by_results[0]["type"], GroupByTypeConst::GROUP_BY_HISTOGRAM);
        $this->assertEquals(count($group_by_results[0]["group_by_result"]["items"]) , 3);
        $item0 = $group_by_results[0]["group_by_result"]["items"][0];
        $this->assertEquals($item0["key"], 3);
        $this->assertEquals($item0["value"], 3);
        $item1 = $group_by_results[0]["group_by_result"]["items"][1];
        $this->assertEquals($item1["key"], 6);
        $this->assertEquals($item1["value"], 3);
        $item2 = $group_by_results[0]["group_by_result"]["items"][2];
        $this->assertEquals($item2["key"], 9);
        $this->assertEquals($item2["value"], 3);
    }

    public function testGroupByWithSub() {
        $request = $this->getBaseRequest();
        $request["search_query"]["group_bys"] = array(
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
                        'sub_aggs' => array(
                            'aggs' => array(
                                array(
                                    'name' => 'AGG_DISTINCT_COUNT_test',
                                    'type' => AggregationTypeConst::AGG_DISTINCT_COUNT,
                                    'body' => array(
                                        'field_name' => 'boolean',
                                        'missing' => true
                                    )
                                ),
                                array(
                                    'name' => 'xx123',
                                    'type' => AggregationTypeConst::AGG_COUNT,
                                    'body' => array(
                                        'field_name' => 'keyword',
                                        'missing' => 'default'
                                    )
                                )
                            ),
                        ),
                        'sub_group_bys' => array(
                            'group_bys' => array(
                                array(
                                    'name' => 'group_by_GROUP_BY_RANGE',
                                    'type' => GroupByTypeConst::GROUP_BY_RANGE,
                                    'body' => array(
                                        'field_name' => 'long',
                                        'ranges' => array(
                                            array(
                                                'from' => 1,
                                                'to' => 3
                                            ),
                                            array(
                                                'from' => 3,
                                                'to' => 7
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
            ),
        );
        $response = $this->otsClient->search($request);
        $group_by_results = $response["group_bys"]["group_by_results"];

        print json_encode($group_by_results, JSON_PRETTY_PRINT);
        $this->assertEquals(count($group_by_results), 1);
        $this->assertEquals($group_by_results[0]["name"], "group_by_GROUP_BY_FIELD");
        $this->assertEquals($group_by_results[0]["type"], GroupByTypeConst::GROUP_BY_FIELD);
        $this->assertEquals(count($group_by_results[0]["group_by_result"]["items"]) , 2);
        $item0 = $group_by_results[0]["group_by_result"]["items"][0];
        $this->assertEquals($item0["key"], "true");
        $this->assertEquals($item0["row_count"], 34);
        { // check sub agg and groupby in group 1
            $item0SubAggs = $item0["sub_aggs_result"]["agg_results"];
            $this->assertEquals(count($item0SubAggs), 2);
            $this->assertEquals($item0SubAggs[0]["name"], "xx123");
            $this->assertEquals($item0SubAggs[0]["type"], AggregationTypeConst::AGG_COUNT);
            $this->assertEquals($item0SubAggs[0]["agg_result"]["value"], 34);
            $this->assertEquals($item0SubAggs[1]["name"], "AGG_DISTINCT_COUNT_test");
            $this->assertEquals($item0SubAggs[1]["type"], AggregationTypeConst::AGG_DISTINCT_COUNT);
            $this->assertEquals($item0SubAggs[1]["agg_result"]["value"], 1);

            $item0SubGroupBys = $item0["sub_group_bys_result"]["group_by_results"];
            $this->assertEquals(count($item0SubGroupBys), 1);
            $this->assertEquals($item0SubGroupBys[0]["name"], "group_by_GROUP_BY_RANGE");
            $this->assertEquals($item0SubGroupBys[0]["type"], GroupByTypeConst::GROUP_BY_RANGE);
            $item0SubGroupBysItems = $item0SubGroupBys[0]["group_by_result"]["items"];
            $this->assertEquals(count($item0SubGroupBysItems), 2);
            $this->assertEquals($item0SubGroupBysItems[0]["row_count"], 0);
            $this->assertEquals($item0SubGroupBysItems[1]["row_count"], 2);
        }

        $item1 = $group_by_results[0]["group_by_result"]["items"][1];
        $this->assertEquals($item1["key"], "false");
        $this->assertEquals($item1["row_count"], 66);
        { // check sub agg and groupby in group 1
            $item1SubAggs = $item1["sub_aggs_result"]["agg_results"];
            $this->assertEquals(count($item1SubAggs), 2);
            $this->assertEquals($item1SubAggs[0]["name"], "xx123");
            $this->assertEquals($item1SubAggs[0]["type"], AggregationTypeConst::AGG_COUNT);
            $this->assertEquals($item1SubAggs[0]["agg_result"]["value"], 66);
            $this->assertEquals($item1SubAggs[1]["name"], "AGG_DISTINCT_COUNT_test");
            $this->assertEquals($item1SubAggs[1]["type"], AggregationTypeConst::AGG_DISTINCT_COUNT);
            $this->assertEquals($item1SubAggs[1]["agg_result"]["value"], 1);

            $item1SubGroupBys = $item1["sub_group_bys_result"]["group_by_results"];
            $this->assertEquals(count($item1SubGroupBys), 1);
            $this->assertEquals($item1SubGroupBys[0]["name"], "group_by_GROUP_BY_RANGE");
            $this->assertEquals($item1SubGroupBys[0]["type"], GroupByTypeConst::GROUP_BY_RANGE);
            $item1SubGroupBysItems = $item1SubGroupBys[0]["group_by_result"]["items"];
            $this->assertEquals(count($item1SubGroupBysItems), 2);
            $this->assertEquals($item1SubGroupBysItems[0]["row_count"], 2);
            $this->assertEquals($item1SubGroupBysItems[1]["row_count"], 2);
        }
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

