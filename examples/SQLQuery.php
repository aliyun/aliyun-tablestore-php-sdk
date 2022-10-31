<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\OTSClient as OTSClient;
use \Aliyun\OTS\Consts\SQLPayloadVersionConst;

$otsClient = new OTSClient (array (
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME
));

$request = array (
    'query' => "SELECT * FROM `WriterTest` LIMIT 10;",
    'version' => SQLPayloadVersionConst::SQL_FLAT_BUFFERS
);

$response = $otsClient->sqlQuery ($request);
$sqlRows = $response['sql_rows'];
$lines = '';
for ($i = 0; $i < $sqlRows->rowCount; $i++) {
    $line = '';
    for ($j = 0; $j < $sqlRows->columnCount; $j++) {
        $line = $line . (is_null($sqlRows->get($j, $i)) ? "null" : $sqlRows->get($j, $i)) . "\t";
    }
    $lines = $lines . $line . "\n";
}
print $lines;
$sqlTableMeta = $sqlRows->getTableMeta();
print json_encode($sqlTableMeta->getSchemaByColumnName("thread_0"), JSON_PRETTY_PRINT);
print json_encode($sqlTableMeta->getSchemaByIndex(1), JSON_PRETTY_PRINT);
print json_encode($sqlTableMeta->getSchemas(), JSON_PRETTY_PRINT);
