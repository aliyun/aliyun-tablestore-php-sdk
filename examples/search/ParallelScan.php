<?php
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../ExampleConfig.php');

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

$computeSplitsPointReq = array(
    'table_name' => 'php_sdk_test',
    'search_index_splits_options' => array(
        'index_name' => 'test_create_search_index'
    )
);

$computeSplits = $otsClient->computeSplits($computeSplitsPointReq);
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
    'table_name' => 'php_sdk_test',
    'index_name' => 'test_create_search_index',
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_ALL_FROM_INDEX, // RETURN_ALL is not allow in parallel_scan, use RETURN_ALL_FROM_INDEX
        'return_names' => array('geo', 'text', 'long', 'keyword')
    ),
    'session_id' => $computeSplits['session_id'],
    'scan_query' => $scanQuery
);

$parallelScanRes = $otsClient->parallelScan($parallelScanReq);
print json_encode ($parallelScanRes['rows'], JSON_PRETTY_PRINT);

$totalCount += count($parallelScanRes['rows']);

while (!is_null($parallelScanRes['next_token'])) {
    $parallelScanReq['scan_query']['token'] = $parallelScanRes['next_token'];
    $parallelScanRes = $otsClient->parallelScan($parallelScanReq);
    print json_encode ($parallelScanRes['rows'], JSON_PRETTY_PRINT);

    $totalCount += count($parallelScanRes['rows']);
}
print "TotalCount: " . $totalCount;