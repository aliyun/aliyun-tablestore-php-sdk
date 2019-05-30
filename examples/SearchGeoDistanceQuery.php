<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\GeoDistanceTypeConst;
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
            'query_type' => QueryTypeConst::GEO_DISTANCE_QUERY,
            'query' => array(
                'field_name' => 'geo',
                'center_point' => '30.001,120.001',
                'distance' => 1000//in center
//                'distance' => 100//out of center
            )
        ),
        'sort' => array(
            array(
                'geo_distance_sort' => array(
                    'field_name' => 'geo',
                    'order' => SortOrderConst::SORT_ORDER_ASC,
                    'distance_type' => GeoDistanceTypeConst::GEO_DISTANCE_PLANE,
                    'points' => array('30,120')
                )
            ),
        )
    ),
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
        'return_names' => array('geo')
    )
));

print json_encode($response, JSON_PRETTY_PRINT);
