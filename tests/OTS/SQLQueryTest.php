<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ColumnReturnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\SQLPayloadVersionConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\SQLStatementTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class SQLQueryTest extends SDKTestBase {

    private static $tableName = 'testSearchTableName';
    private static $indexName = 'testSearchIndexName';
    private static $sqlTableName = 'testSearchTableName';
    private static $createSqlTableSql = 'CREATE TABLE `testSearchTableName` (`PK0` BIGINT(20),`PK1` VARCHAR(1024),`geo` MEDIUMTEXT,`boolean` BOOL,`array` MEDIUMTEXT,`double` DOUBLE,`text` MEDIUMTEXT,`keyword` MEDIUMTEXT,`nested` MEDIUMTEXT,`long` BIGINT(20),PRIMARY KEY(`PK0`,`PK1`));';

    public static function setUpBeforeClass()
    {

        $createTableRequest = array (
            'table_meta' => array (
                'table_name' => self::$tableName, // 表名为 MyTable
                'primary_key_schema' => array (
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,   // 数据生命周期, -1表示永久，单位秒
                'max_versions' => 1,    // 最大数据版本
                'deviation_cell_version_in_sec' => 86400  // 数据有效版本偏差，单位秒
            )
        );
        SDKTestBase::createInitialTable($createTableRequest);

        self::createIndex();
        self::insertData();
        self::waitForSearchIndexSync();
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUpSearchIndex(self::$tableName);
        SDKTestBase::cleanUp(array(self::$tableName));
    }

    public function testCreateDeleteAndShowSQLTable() {
        {
            $request = array(
                'query' => "SHOW TABLES;",
            );
            $response = $this->otsClient->sqlQuery ($request);
            $sqlRows = $response['sql_rows'];
            $meta = $sqlRows->getTableMeta();
            $this->assertEquals($sqlRows->getRowCount(),0);
            $this->assertEquals($sqlRows->getColumnCount(), 1);
            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_SHOW_TABLE);
            print json_encode($sqlRows, JSON_PRETTY_PRINT);
        }
        {
            $request = array(
                'query' => self::$createSqlTableSql,
            );
            $response = $this->otsClient->sqlQuery($request);
            $this->assertNull($response['sql_rows']);
            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_CREATE_TABLE);
            print json_encode($response, JSON_PRETTY_PRINT);
        }
        {
            $request = array(
                'query' => "SHOW TABLES;",
            );
            $response = $this->otsClient->sqlQuery ($request);
            $sqlRows = $response['sql_rows'];
            $meta = $sqlRows->getTableMeta();
            $this->assertEquals($sqlRows->getRowCount(),1);
            $this->assertEquals($sqlRows->getColumnCount(), 1);
            $this->assertEquals($meta->getSchemaByColumnName("Tables_in_phpsdkinstance")["index"], 0);
            $this->assertEquals($sqlRows->get(0, 0), self::$sqlTableName);
            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_SHOW_TABLE);
            print json_encode($sqlRows, JSON_PRETTY_PRINT);
        }
        {
            $request = array(
                'query' => 'DROP MAPPING TABLE `testSearchTableName`;'
            );
            $response = $this->otsClient->sqlQuery($request);
            $this->assertNull($response['sql_rows']);
            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_DROP_TABLE);
            print json_encode($sqlRows, JSON_PRETTY_PRINT);
        }
        {
            $request = array(
                'query' => "SHOW TABLES;",
            );
            $response = $this->otsClient->sqlQuery ($request);
            $sqlRows = $response['sql_rows'];
            $meta = $sqlRows->getTableMeta();
            $this->assertEquals($sqlRows->getRowCount(),0);
            $this->assertEquals($sqlRows->getColumnCount(), 1);
            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_SHOW_TABLE);
            print json_encode($sqlRows, JSON_PRETTY_PRINT);
        }
    }

    public function testSQLQuerySelect() {
        { // init
            $createRequest = array(
                'query' => self::$createSqlTableSql,
            );
            try {
                $this->otsClient->sqlQuery($createRequest);
            } catch (\Aliyun\OTS\OTSServerException $exc) {
            }
        }

        {
            $request = array(
                'query' => 'SELECT COUNT(*) AS count FROM `testSearchTableName` WHERE `boolean` = true;',
            );
            $response = $this->otsClient->sqlQuery($request);
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
            $sqlRows = $response['sql_rows'];
            $meta = $sqlRows->getTableMeta();
            $this->assertEquals($sqlRows->getRowCount(),1);
            $this->assertEquals($sqlRows->getColumnCount(), 1);
            $this->assertEquals($sqlRows->get(0, 0), 34);

            $this->assertEquals($meta->getSchemaByColumnName('count')['index'], 0);
            $this->assertEquals($meta->getSchemaByIndex(0)['name'], 'count');

            $searchConsumes = $response["search_consumes"];
            $this->assertEquals(count($searchConsumes), 1);
            {
                $item = $searchConsumes[0];
                $this->assertEquals($item["table_name"], self::$sqlTableName);
                $this->assertEquals($item["reserved_throughput"]["capacity_unit"]["read"], 0); // 新创建的索引预留cu不能及时更新20，已验证老索引有效
                $this->assertEquals($item["reserved_throughput"]["capacity_unit"]["write"], 0);
                $this->assertEquals($item["consumed"]["capacity_unit"]["read"], 1);
                $this->assertEquals($item["consumed"]["capacity_unit"]["write"], 0);
            }
            $consumes = $response["consumes"];
            $this->assertEquals(count($consumes), 0);

            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_SELECT);
        }
        {
            $request = array(
                'query' => 'SELECT `PK0`, `boolean`, `long`, `geo` FROM `testSearchTableName` LIMIT 10;',
            );
            $response = $this->otsClient->sqlQuery($request);
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
            $sqlRows = $response['sql_rows'];
            $meta = $sqlRows->getTableMeta();
            $this->assertEquals($sqlRows->getRowCount(),10);
            $this->assertEquals($sqlRows->getColumnCount(), 4);
            $this->assertEquals($sqlRows->get(0, 0), 0);
            $this->assertTrue($sqlRows->get(1, 0));
            $this->assertFalse($sqlRows->get(1, 1));
            $this->assertEquals($sqlRows->get(2, 2), 2);
            $this->assertEquals($sqlRows->get(3, 3), '5.3,6.3');

            $searchConsumes = $response["search_consumes"];
            $this->assertEquals(count($searchConsumes), 0);
            $consumes = $response["consumes"];
            $this->assertEquals(count($consumes), 1);
            {
                $item = $consumes[0];
                $this->assertEquals($item["table_name"], self::$sqlTableName);
                $this->assertEquals($item["reserved_throughput"]["capacity_unit"]["read"], 0);
                $this->assertEquals($item["reserved_throughput"]["capacity_unit"]["write"], 0);
                $this->assertEquals($item["consumed"]["capacity_unit"]["read"], 1);
                $this->assertEquals($item["consumed"]["capacity_unit"]["write"], 0);
            }

            $this->assertEquals($response['type'], SQLStatementTypeConst::DCT_SQL_SELECT);
        }
        { // drop
            $createRequest = array(
                'query' => 'DROP MAPPING TABLE `testSearchTableName`;',
            );
            try {
                $this->otsClient->sqlQuery($createRequest);
            } catch (\Aliyun\OTS\OTSServerException $exc) {
            }
        }
    }

    public static function createIndex() {
        $createIndexRequest = array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
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
                    array(
                        'field_name' => 'geo',
                        'field_type' => FieldTypeConst::GEO_POINT,
                        'index' => true,
                        'index_options' => 'DOCS',
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'long',
                        'field_type' => FieldTypeConst::LONG,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'double',
                        'field_type' => FieldTypeConst::DOUBLE,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'boolean',
                        'field_type' => FieldTypeConst::BOOLEAN,
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => false
                    ),
                    array(
                        'field_name' => 'array',
                        'field_type' => FieldTypeConst::KEYWORD,
                        'index' => true,
                        'enable_sort_and_agg' => false,
                        'store' => true,
                        'is_array' => true
                    ),
                    array(
                        'field_name' => 'nested',
                        'field_type' => FieldTypeConst::NESTED,
                        'index' => false,
                        'enable_sort_and_agg' => false,
                        'store' => false,
                        'field_schemas' => array(
                            array(
                                'field_name' => 'nested_keyword',
                                'field_type' => FieldTypeConst::KEYWORD,
                                'index' => true,
                                'enable_sort_and_agg' => true,
                                'store' => true,
                                'is_array' => false
                            ),
                            array(
                                'field_name' => 'nested_long',
                                'field_type' => FieldTypeConst::LONG,
                                'index' => true,
                                'enable_sort_and_agg' => true,
                                'store' => true,
                                'is_array' => false
                            ),
                        )
                    ),
                ),
                'index_setting' => array(
                    'routing_fields' => array("PK0")
                )
            )
        );

        SDKTestBase::createSearchIndex($createIndexRequest);
    }

    private static function insertData() {
        for ($i = 0; $i < 100; $i++) {
            $request = array(
                'table_name' => self::$tableName,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array ( // 主键
                    array('PK0', $i),
                    array('PK1', 'search')
                ),
                'attribute_columns' => array(
                    array('keyword', 'keyword'),
                    array('text', 'ots php search index' . $i),
                    array('geo', '5.' . $i . ',6.' . $i),
                    array('long', $i),
                    array('double', $i + 0.1),
                    array('boolean', $i % 3 == 0),
                    array('array', '["search","index' . $i . '"]'),
                    array('nested', '[{"nested_keyword":"sub","nested_long":' . $i . '}]')
                )
            );

            SDKTestBase::putInitialData($request);
        }
    }
}

