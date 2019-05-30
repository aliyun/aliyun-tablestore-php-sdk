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
        'limit' => 10,
        'get_total_count' => true,
        'collapse' => array(
            'field_name' => 'keyword'
        ),
        'query' => array(
            'query_type' => QueryTypeConst::MATCH_ALL_QUERY
        ),
//        'sort' => array(
//            array(
//                'field_sort' => array(
//                    'field_name' => 'keyword',
//                    'order' => SortOrderConst::SORT_ORDER_ASC
//                )
//            ),
//        ),
        'token' => null,
    ),
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
        'return_names' => array('col1', 'col2')
    )
));

print json_encode($response, JSON_PRETTY_PRINT);
