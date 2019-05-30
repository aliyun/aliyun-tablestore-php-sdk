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


$request = array(
    'table_name' => 'php_sdk_test',
    'index_name' => 'test_create_search_index',
    'search_query' => array(
        'offset' => 0,
        'limit' => 1,
        'get_total_count' => true,
        'query' => array(
            'query_type' => QueryTypeConst::FUNCTION_SCORE_QUERY,
            'query' => array(
                'query' => array(
                    'query_type' => QueryTypeConst::TERM_QUERY,
                    'query' => array(
                        'field_name' => 'keyword',
                        'term' => 'keyword'
                    )
                ),
                'field_value_factor' => array(
                    'field_name' => 'long'
                )
            )
        ),
        'sort' => array(
            array(
                'score_sort' => array(
                    'order' => SortOrderConst::SORT_ORDER_DESC
                )
            ),
        )
    ),
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
        'return_names' => array('keyword', 'long')
    )
);

$response = $otsClient->search($request);
print "total_hits: " . $response['total_hits'] . "\n";
print json_encode($response['rows'], JSON_PRETTY_PRINT);

while($response['next_token'] != null) {
    $request['search_query']['token'] = $response['next_token'];
    $request['search_query']['sort'] = null;
    $response = $otsClient->search($request);
    print json_encode($response['rows'], JSON_PRETTY_PRINT);
}
//print json_encode($response['next_token'], JSON_PRETTY_PRINT);
