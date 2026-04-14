<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\AsyncResponse;
use Aliyun\OTS\Consts\ColumnReturnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\QueryTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\SortOrderConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class AsyncResponseTest extends SDKTestBase
{
    private static $tableName = 'testAsyncTable';
    private static $indexName = 'testAsyncIndex';

    public static function setUpBeforeClass(): void
    {
        $createTableRequest = array(
            'table_meta' => array(
                'table_name' => self::$tableName,
                'primary_key_schema' => array(
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'reserved_throughput' => array(
                'capacity_unit' => array(
                    'read' => 0,
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 1,
                'deviation_cell_version_in_sec' => 86400
            )
        );
        SDKTestBase::createInitialTable($createTableRequest);

        self::createIndex();
        self::insertData();
        self::waitForSearchIndexSync();
    }

    public static function tearDownAfterClass(): void
    {
        SDKTestBase::cleanUpSearchIndex(self::$tableName);
        SDKTestBase::cleanUp(array(self::$tableName));
    }

    /**
     * Test asyncSearch returns AsyncResponse and wait() works.
     */
    public function testAsyncSearchWait()
    {
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 10,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_ALL_QUERY,
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                ),
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
            ),
        );

        $asyncResponse = $this->otsClient->asyncSearch($request);

        $this->assertInstanceOf(AsyncResponse::class, $asyncResponse);
        $this->assertFalse($asyncResponse->isResolved());

        $result = $asyncResponse->wait();

        $this->assertTrue($asyncResponse->isResolved());
        $this->assertTrue($asyncResponse->isSuccessful());
        $this->assertFalse($asyncResponse->isFailed());
        $this->assertNull($asyncResponse->getException());
        $this->assertIsArray($result);
        $this->assertTrue($result['is_all_succeeded']);
    }

    /**
     * Test AsyncResponse as transparent array proxy.
     */
    public function testAsyncSearchArrayAccess()
    {
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 5,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_ALL_QUERY,
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                ),
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
            ),
        );

        $asyncResponse = $this->otsClient->asyncSearch($request);

        // ArrayAccess — auto resolves
        $this->assertTrue(isset($asyncResponse['is_all_succeeded']));
        $this->assertTrue($asyncResponse['is_all_succeeded']);
        $this->assertTrue($asyncResponse->isResolved());

        // Countable
        $this->assertGreaterThan(0, count($asyncResponse));

        // IteratorAggregate
        $keys = [];
        foreach ($asyncResponse as $key => $value) {
            $keys[] = $key;
        }
        $this->assertContains('rows', $keys);
    }

    /**
     * Test concurrent async requests.
     */
    public function testConcurrentAsyncSearch()
    {
        $request = array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 10,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_ALL_QUERY,
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                ),
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
            ),
        );

        // Fire 5 concurrent requests
        $contexts = [];
        for ($i = 0; $i < 5; $i++) {
            $contexts[$i] = $this->otsClient->asyncSearch($request);
        }

        // None should be resolved yet (requests are in-flight)
        foreach ($contexts as $ctx) {
            $this->assertInstanceOf(AsyncResponse::class, $ctx);
        }

        // Wait for all and verify results
        foreach ($contexts as $i => $ctx) {
            $result = $ctx->wait();
            $this->assertIsArray($result);
            $this->assertTrue($result['is_all_succeeded']);
            $this->assertEquals(20, $result['total_hits']);
        }
    }

    /**
     * Test asyncDoHandle with ListTable.
     */
    public function testAsyncDoHandle()
    {
        $asyncResponse = $this->otsClient->asyncDoHandle('ListTable', array());

        $result = $asyncResponse->wait();
        $this->assertIsArray($result);
        $this->assertContains(self::$tableName, $result);
    }

    /**
     * Test HWait() backward compatibility.
     */
    public function testHWaitBackwardCompatibility()
    {
        $asyncResponse = $this->otsClient->asyncDoHandle('ListTable', array());

        $result = $asyncResponse->HWait();
        $this->assertIsArray($result);
        $this->assertContains(self::$tableName, $result);
    }

    /**
     * Test that wait() returns cached result on second call.
     */
    public function testWaitCachesResult()
    {
        $asyncResponse = $this->otsClient->asyncDoHandle('ListTable', array());

        $result1 = $asyncResponse->wait();
        $result2 = $asyncResponse->wait();
        $this->assertSame($result1, $result2);
    }

    /**
     * Test asyncSqlQuery.
     */
    public function testAsyncSqlQuery()
    {
        $asyncResponse = $this->otsClient->asyncSqlQuery(array(
            'query' => 'SHOW TABLES;',
        ));

        $this->assertInstanceOf(AsyncResponse::class, $asyncResponse);
        $result = $asyncResponse->wait();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
    }

    /**
     * Test concurrent asyncSqlQuery requests.
     */
    public function testConcurrentAsyncSqlQuery()
    {
        $contexts = [];
        for ($i = 0; $i < 3; $i++) {
            $contexts[$i] = $this->otsClient->asyncSqlQuery(array(
                'query' => 'SHOW TABLES;',
            ));
        }

        foreach ($contexts as $ctx) {
            $result = $ctx->wait();
            $this->assertIsArray($result);
            $this->assertArrayHasKey('type', $result);
        }
    }

    private static function createIndex()
    {
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
                        'field_name' => 'long',
                        'field_type' => FieldTypeConst::LONG,
                        'index' => true,
                        'enable_sort_and_agg' => true,
                        'store' => true,
                        'is_array' => false
                    ),
                ),
            )
        );
        SDKTestBase::createSearchIndex($createIndexRequest);
    }

    private static function insertData()
    {
        for ($i = 0; $i < 20; $i++) {
            $request = array(
                'table_name' => self::$tableName,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array(
                    array('PK0', $i),
                    array('PK1', 'async')
                ),
                'attribute_columns' => array(
                    array('keyword', 'test_keyword_' . $i),
                    array('long', $i),
                )
            );
            SDKTestBase::putInitialData($request);
        }
    }
}
