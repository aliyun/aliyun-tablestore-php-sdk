<?php


require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../ExampleConfig.php');
require(__DIR__ . '/TablestoreUtil.php');

use Aliyun\OTS\OTSClient as OTSClient;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;

$tableName = "SearchIndexTable";
$indexName = "SearchIndexName";
$reindexName = "SearchIndexName_reindex";
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

$reindexRequest = array(
    'table_name' => $tableName,
    'index_name' => $reindexName,
    'source_index_name' => $indexName,
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
        )
    )
);

//$otsClient->createSearchIndex($reindexRequest);

$describeSearchIndex = $otsClient->describeSearchIndex(array(
    'table_name' => $tableName,
    'index_name' => $indexName,
));

$util->deleteSearchIndexReindex();


