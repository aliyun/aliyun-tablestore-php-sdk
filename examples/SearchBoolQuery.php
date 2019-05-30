<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\QueryTypeConst;
use Aliyun\OTS\Consts\ColumnReturnTypeConst;
use Aliyun\OTS\Consts\SortOrderConst;

$otsClient = new OTSClient(array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME,
));


$response = $otsClient->search(array(
    'table_name' => 'php_sdk_test',
    'index_name' => 'test_create_search_index',
    'search_query' => array(
        'offset' => 0,
        'limit' => 2,
        'get_total_count' => true,
        'query' => array(
            'query_type' => QueryTypeConst::BOOL_QUERY,
            'query' => array(
//                'must_queries' => array(
//                    array(
//                        'query_type' => QueryTypeConst::TERM_QUERY,
//                        'query' => array(
//                            'field_name' => 'keyword',
//                            'term' => 'keyword'
//                        )
//                    ),
//                    array(
//                        'query_type' => QueryTypeConst::RANGE_QUERY,
//                        'query' => array(
//                            'field_name' => 'long',
//                            'range_from' => 100,
//                            'include_lower' => true,
//                            'range_to' => 101,
//                            'include_upper' => false
//                        )
//                    )
//                ),
//                'must_not_queries' => array(
//                        array(
//                            'query_type' => QueryTypeConst::TERM_QUERY,
//                            'query' => array(
//                                'field_name' => 'keyword',
//                                'term' => 'keyword'
//                            )
//                        ),
//                        array(
//                            'query_type' => QueryTypeConst::RANGE_QUERY,
//                            'query' => array(
//                                'field_name' => 'long',
//                                'range_from' => 100,
//                                'include_lower' => true,
//                                'range_to' => 101,
//                                'include_upper' => false
//                            )
//                        )
//                    ),
//                'filter_queries' => array(
//                    array(
//                        'query_type' => QueryTypeConst::TERM_QUERY,
//                        'query' => array(
//                            'field_name' => 'keyword',
//                            'term' => 'keyword'
//                        )
//                    ),
//                    array(
//                        'query_type' => QueryTypeConst::RANGE_QUERY,
//                        'query' => array(
//                            'field_name' => 'long',
//                            'range_from' => 100,
//                            'include_lower' => true,
//                            'range_to' => 101,
//                            'include_upper' => false
//                        )
//                    )
//                ),
                'should_queries' => array(
                    array(
                        'query_type' => QueryTypeConst::TERM_QUERY,
                        'query' => array(
                            'field_name' => 'keyword',
                            'term' => 'keyword'
                        )
                    ),
                    array(
                        'query_type' => QueryTypeConst::RANGE_QUERY,
                        'query' => array(
                            'field_name' => 'long',
                            'range_from' => 100,
                            'include_lower' => true,
                            'range_to' => 101,
                            'include_upper' => false
                        )
                    )
                ),
                'minimum_should_match' => 1
            )
        ),
        'sort' => array(
            array(
                'field_sort' => array(
                    'field_name' => 'keyword',
                    'order' => SortOrderConst::SORT_ORDER_ASC
                )
            ),
        )
    ),
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
        'return_names' => array('keyword', 'long')
    )
));

print json_encode($response, JSON_PRETTY_PRINT);
