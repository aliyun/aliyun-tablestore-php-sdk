<?php

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../ExampleConfig.php');
require(__DIR__ . '/TablestoreUtil.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\QueryTypeConst;
use Aliyun\OTS\Consts\ColumnReturnTypeConst;
use Aliyun\OTS\Consts\GroupByTypeConst;
use Aliyun\OTS\Consts\AggregationTypeConst;
use Aliyun\OTS\Consts\SortOrderConst;


$tableName = "SearchIndexTable";
$indexName = "SearchIndexName";
$otsClient = new OTSClient (array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

$util = new TablestoreUtil($otsClient, $tableName, $indexName);

$describeSearchIndex = $otsClient->describeSearchIndex(array(
    'table_name' => $tableName,
    'index_name' => $indexName,
));

$request = array(
    'table_name' => $tableName,
    'index_name' => $indexName,
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
                        'min_doc_count' => 10,
                        'sort' => array(
                            'sorters' => array(
                                array(
                                    'group_key_sort' => array(
                                        'order' => SortOrderConst::SORT_ORDER_DESC
                                    ),
                                    'row_count_sort' => array(
                                        'order' => SortOrderConst::SORT_ORDER_ASC
                                    ),
                                    'sub_agg_sort' => array(
                                        'sub_agg_name' => 'xx123',
                                        'order' => SortOrderConst::SORT_ORDER_ASC
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
                array(
                    'name' => 'group_by_GROUP_BY_HISTOGRAM',
                    'type' => GroupByTypeConst::GROUP_BY_HISTOGRAM,
                    'body' => array(
                        'field_name' => 'long',
                        'interval' => 3,
                        'missing' => 0,
                        'min_doc_count' => 10,
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
                )
            ),
        ),
        'aggs' => array(
            'aggs' => array(
                array(
                    'name' => 'avg_test',
                    'type' => AggregationTypeConst::AGG_AVG,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
                array(
                    'name' => 'avg_max',
                    'type' => AggregationTypeConst::AGG_MAX,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
                array(
                    'name' => 'avg_min',
                    'type' => AggregationTypeConst::AGG_MIN,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
                array(
                    'name' => 'avg_sum',
                    'type' => AggregationTypeConst::AGG_SUM,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
                array(
                    'name' => 'avg_count',
                    'type' => AggregationTypeConst::AGG_COUNT,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
                array(
                    'name' => 'avg_distinct_count',
                    'type' => AggregationTypeConst::AGG_DISTINCT_COUNT,
                    'body' => array(
                        'field_name' => 'long',
                        'missing' => 0
                    )
                ),
                array(
                    'name' => 'avg_top_rows',
                    'type' => AggregationTypeConst::AGG_TOP_ROWS,
                    'body' => array(
                        'limit' => 1,
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
            )
        )
    ),
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_ALL_FROM_INDEX,
    )
);

$response = $otsClient->search($request);

print json_encode ($response, JSON_PRETTY_PRINT);
