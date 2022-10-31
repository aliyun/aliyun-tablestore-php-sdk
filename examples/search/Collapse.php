<?php

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../ExampleConfig.php');
require(__DIR__ . '/TablestoreUtil.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\QueryTypeConst;
use Aliyun\OTS\Consts\ColumnReturnTypeConst;

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
        'limit' => 11,
        'get_total_count' => true,
        'query' => array(
            'query_type' => QueryTypeConst::MATCH_ALL_QUERY
        ),
        'token' => null,
    ),
    'columns_to_get' => array(
        'return_type' => ColumnReturnTypeConst::RETURN_ALL_FROM_INDEX,
    )
);

$response = $otsClient->search($request);
print "\nnot collapse length: " . count($response["rows"]) . "\n";

// 设置折叠后有，结果集会基于该字段去重，虽然返回11行，但是实际只有10个不同的行
$request["search_query"]["collapse"] =  array(
    'field_name' => 'long'
);
$response = $otsClient->search($request);
//print json_encode ($response, JSON_PRETTY_PRINT);
print "\ncollapse length: " . count($response["rows"]) . "\n";