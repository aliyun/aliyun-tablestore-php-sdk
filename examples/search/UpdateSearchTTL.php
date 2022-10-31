<?php


require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../ExampleConfig.php');
require(__DIR__ . '/TablestoreUtil.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;

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

// 修改表不支持更新，索引设置ttl的前提
$otsClient->updateTable(array(
    'table_name' => $tableName,
    'table_options' => array(
        'allow_update' => false  // 是否允许更新
    ),
));

$otsClient->updateSearchIndex(array(
    'table_name' => $tableName,
    'index_name' => $indexName,
    'time_to_live' => 8000000
));

// 修改成功，获取索引TTL
// "time_to_live":8000000
$describeSearchIndex = $otsClient->describeSearchIndex(array(
    'table_name' => $tableName,
    'index_name' => $indexName,
));


$otsClient->updateSearchIndex(array(
    'table_name' => $tableName,
    'index_name' => $indexName,
    'time_to_live' => -1
));

// 结束重新将表是否允许更新设置回默认值
$otsClient->updateTable(array(
    'table_name' => $tableName,
    'table_options' => array(
        'allow_update' => true  // 是否允许更新
    ),
));
