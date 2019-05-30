<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\ColumnReturnTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\QueryOperatorConst;
use Aliyun\OTS\Consts\QueryTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\ScoreModeConst;
use Aliyun\OTS\Consts\SortModeConst;
use Aliyun\OTS\Consts\SortOrderConst;
use Aliyun\OTS\Consts\GeoDistanceTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class SearchIndexSearchTest extends SDKTestBase {

    private static $tableName = 'testSearchTableName';
    private static $indexName = 'testSearchIndexName';

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

    public function testMatchQueryOr() {//1
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_QUERY,
                    'query' => array(
                        'field_name' => 'text',
                        'text' => 'ots text php index0',
                        'operator' => QueryOperatorConst::PBOR,
                        'minimum_should_match' => 2
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('text')
            )
        ));
//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testMatchQueryAnd() {//1
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_QUERY,
                    'query' => array(
                        'field_name' => 'text',
                        'text' => 'ots php index0',
                        'operator' => QueryOperatorConst::PBAND,
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('text')
            )
        ));
//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testMatchPhraseQueryHas() {//2
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_PHRASE_QUERY,
                    'query' => array(
                        'field_name' => 'text',
                        'text' => 'search index0'
//                      'text' => 'index0 search'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('text')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testMatchPhraseQueryNotHas() {//2
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_PHRASE_QUERY,
                    'query' => array(
                        'field_name' => 'text',
                        'text' => 'index0 search'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('text')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 0);
        $this->assertEquals(count($response['rows']), 0);
        $this->assertEmpty($response['next_token']);
    }

    public function testTermQueryKeyword() {//3
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::TERM_QUERY,
                    'query' => array(
                        'field_name' => 'keyword',
                        'term' => 'keyword'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
                'return_names' => array('keyword', 'array')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testTermQueryDouble() {//3
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::TERM_QUERY,
                    'query' => array(
                        'field_name' => 'double',
                        'term' => 0.1
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
                'return_names' => array('keyword', 'array')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testTermQueryLong() {//3
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::TERM_QUERY,
                    'query' => array(
                        'field_name' => 'long',
                        'term' => 0
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
                'return_names' => array('keyword', 'array')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testTermQueryBoolean() {//3
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::TERM_QUERY,
                    'query' => array(
                        'field_name' => 'boolean',
                        'term' => true
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
                'return_names' => array('keyword', 'array')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 3);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testTermQueryArray() {//3
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::TERM_QUERY,
                    'query' => array(
                        'field_name' => 'array',
                        'term' => 'index0'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
                'return_names' => array('keyword', 'array')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testRangeQuery2() {//4
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::RANGE_QUERY,
                    'query' => array(
                        'field_name' => 'long',
                        'range_from' => 1,
                        'include_lower' => true,
                        'range_to' => 3,
                        'include_upper' => false
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('double', 'long', 'keyword')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 2);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testRangeQuery1() {//4
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::RANGE_QUERY,
                    'query' => array(
                        'field_name' => 'long',
                        'range_from' => 1,
                        'include_lower' => false,
                        'range_to' => 3,
                        'include_upper' => false
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('double', 'long', 'keyword')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testPrefixQuery() {//5
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::PREFIX_QUERY,
                    'query' => array(
                        'field_name' => 'keyword',
                        'prefix' => 'key'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_ALL,
                'return_names' => array('keyword')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testBoolQueryMust() {//6
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::BOOL_QUERY,
                    'query' => array(
                        'must_queries' => array(
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => true
                                )
                            ),
                            array(
                                'query_type' => QueryTypeConst::RANGE_QUERY,
                                'query' => array(
                                    'field_name' => 'long',
                                    'range_from' => 1,
                                    'include_lower' => true,
                                    'range_to' => 3,
                                    'include_upper' => false
                                )
                            )
                        ),
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('boolean', 'long')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testBoolQueryMustNot() {//6
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::BOOL_QUERY,
                    'query' => array(
                        'must_not_queries' => array(
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => true
                                )
                            ),
                            array(
                                'query_type' => QueryTypeConst::RANGE_QUERY,
                                'query' => array(
                                    'field_name' => 'long',
                                    'range_from' => 1,
                                    'include_lower' => true,
                                    'range_to' => 3,
                                    'include_upper' => false
                                )
                            )
                        ),
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('boolean', 'long')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testBoolQueryFilter() {//6
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::BOOL_QUERY,
                    'query' => array(
                        'filter_queries' => array(
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => true
                                )
                            ),
                            array(
                                'query_type' => QueryTypeConst::RANGE_QUERY,
                                'query' => array(
                                    'field_name' => 'long',
                                    'range_from' => 1,
                                    'include_lower' => true,
                                    'range_to' => 3,
                                    'include_upper' => false
                                )
                            )
                        ),
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('boolean', 'long')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testBoolQueryShouldMin1() {//6
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::BOOL_QUERY,
                    'query' => array(
                        'should_queries' => array(
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => true
                                )
                            ),
                            array(
                                'query_type' => QueryTypeConst::RANGE_QUERY,
                                'query' => array(
                                    'field_name' => 'long',
                                    'range_from' => 1,
                                    'include_lower' => true,
                                    'range_to' => 3,
                                    'include_upper' => false
                                )
                            )
                        ),
                        'minimum_should_match' => 1
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('boolean', 'long')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 4);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testBoolQueryShouldMin2() {//6
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::BOOL_QUERY,
                    'query' => array(
                        'should_queries' => array(
                            array(
                                'query_type' => QueryTypeConst::TERM_QUERY,
                                'query' => array(
                                    'field_name' => 'boolean',
                                    'term' => true
                                )
                            ),
                            array(
                                'query_type' => QueryTypeConst::RANGE_QUERY,
                                'query' => array(
                                    'field_name' => 'long',
                                    'range_from' => 1,
                                    'include_lower' => true,
                                    'range_to' => 3,
                                    'include_upper' => false
                                )
                            )
                        ),
                        'minimum_should_match' => 2
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('boolean', 'long')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 1);
        $this->assertEquals(count($response['rows']), 1);
        $this->assertEmpty($response['next_token']);
    }

    public function testConstScoreQuery() {//7
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::CONST_SCORE_QUERY,
                    'query' => array(
                        'filter' => array(
                            'query_type' => QueryTypeConst::TERM_QUERY,
                            'query' => array(
                                'field_name' => 'keyword',
                                'term' => 'keyword'
                            )
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
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testFunctionScoreQuery() {//8
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
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
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertEquals($response['rows'][0]['attribute_columns'][1][1], 4);//score_sort DESC
        $this->assertNotEmpty($response['next_token']);
    }

    public function testNestedQueryTerm() {//9
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::NESTED_QUERY,
                    'score_mode' => ScoreModeConst::SCORE_MODE_AVG,
                    'query' => array(
                        'path' => "nested",
                        'query' => array(
                            'query_type' => QueryTypeConst::TERM_QUERY,
                            'query' => array(
                                'field_name' => 'nested.nested_keyword',
                                'term' => 'sub'
                            )
                        )
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'nested.nested_long',
                            'order' => SortOrderConst::SORT_ORDER_DESC,
                            'nested_filter' => array(
                                'path' => "nested",
                                'query' => array(
                                    'query_type' => QueryTypeConst::TERM_QUERY,
                                    'query' => array(
                                        'field_name' => 'nested.nested_keyword',
                                        'term' => 'sub'
                                    )
                                )
                            )
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('nested')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertEquals($response['rows'][0]['attribute_columns'][0][1], '[{"nested_keyword":"sub","nested_long":4}]');
        $this->assertNotEmpty($response['next_token']);
    }

    public function testNestedQueryExists() {//9
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::NESTED_QUERY,
                    'query' => array(
                        'path' => "nested",
                        'query' => array(
                            'query_type' => QueryTypeConst::EXISTS_QUERY,
                            'query' => array(
                                'field_name' => 'nested.nested_long',
                            )
                        ),
                        'score_mode' => ScoreModeConst::SCORE_MODE_AVG
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('nested')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testWildcardQuery() {//10
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::WILDCARD_QUERY,
                    'query' => array(
                        'field_name' => 'array',
                        'value' => 'index*'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('keyword')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testMatchAllQuery() {//11
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 3,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_ALL_QUERY
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_DESC
                        )
                    ),
                ),
                'token' => null,
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('col1', 'col2')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 3);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testGeoBoundingBoxQuery() {//12
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::GEO_BOUNDING_BOX_QUERY,
                    'query' => array(
                        'field_name' => 'geo',
                        'top_left' => '2,-2',
                        'bottom_right' => '-2,2'
                    )
                ),
                'sort' => array(
                    array(
                        'geo_distance_sort' => array(
                            'field_name' => 'geo',
                            'order' => SortOrderConst::SORT_ORDER_ASC,
                            'distance_type' => GeoDistanceTypeConst::GEO_DISTANCE_PLANE,
                            'points' => array('0.6,0.6')
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('geo')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 3);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertEquals($response['rows'][0]['attribute_columns'][0][1], '1,1');
        $this->assertNotEmpty($response['next_token']);
    }

    public function testGeoDistanceQuery() {//13
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::GEO_DISTANCE_QUERY,
                    'query' => array(
                        'field_name' => 'geo',
                        'center_point' => '0.6,0.6',
                        'distance' => 100000//in center
                    )
                ),
                'sort' => array(
                    array(
                        'geo_distance_sort' => array(
                            'field_name' => 'geo',
                            'order' => SortOrderConst::SORT_ORDER_ASC,
                            'distance_type' => GeoDistanceTypeConst::GEO_DISTANCE_PLANE,
                            'points' => array('0.6,0.6')
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('geo')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 2);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertEquals($response['rows'][0]['attribute_columns'][0][1], '1,1');
        $this->assertNotEmpty($response['next_token']);
    }

    public function testGeoPolygonQuery() {//14
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::GEO_POLYGON_QUERY,
                    'query' => array(
                        'field_name' => 'geo',
                        'points' => array(
                            "3,3",
                            "1,0",
                            "1.5,1.5",
                            "0,1"
                        )
                    )
                ),
                'sort' => array(
                    array(
                        'geo_distance_sort' => array(
                            'field_name' => 'geo',
                            'order' => SortOrderConst::SORT_ORDER_ASC,
                            'distance_type' => GeoDistanceTypeConst::GEO_DISTANCE_PLANE,
                            'points' => array('0.6,0.6')
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('geo')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 2);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertEquals($response['rows'][0]['attribute_columns'][0][1], '2,2');
        $this->assertNotEmpty($response['next_token']);
    }

    public function testTermsQuery() {//15
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::TERMS_QUERY,
                    'query' => array(
                        'field_name' => 'long',
                        'terms' => array(
                            1, 2, 3
                        )
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_DESC,
                            'mode' => SortModeConst::SORT_MODE_AVG
                        )
                    )
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('keyword', 'long')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 3);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testExistsQuery() {//16
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 2,
                'get_total_count' => true,
                'query' => array(
                    'query_type' => QueryTypeConst::EXISTS_QUERY,
                    'query' => array(
                        'field_name' => 'text'
                    )
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'keyword',
                            'order' => SortOrderConst::SORT_ORDER_ASC
                        )
                    ),
                )
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_SPECIFIED,
                'return_names' => array('keyword')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], 5);
        $this->assertEquals(count($response['rows']), 2);
        $this->assertNotEmpty($response['next_token']);
    }

    public function testNotReturnAllWithNoToken() {
        $response = $this->otsClient->search(array(
            'table_name' => self::$tableName,
            'index_name' => self::$indexName,
            'search_query' => array(
                'offset' => 0,
                'limit' => 10,
                'get_total_count' => false,
                'query' => array(
                    'query_type' => QueryTypeConst::MATCH_ALL_QUERY
                ),
                'sort' => array(
                    array(
                        'field_sort' => array(
                            'field_name' => 'long',
                            'order' => SortOrderConst::SORT_ORDER_DESC
                        )
                    ),
                ),
                'token' => null,
            ),
            'columns_to_get' => array(
                'return_type' => ColumnReturnTypeConst::RETURN_NONE,
                'return_names' => array('col1', 'col2')
            )
        ));

//        print json_encode($response, JSON_PRETTY_PRINT);
        $this->assertTrue($response['is_all_succeeded']);
        $this->assertEquals($response['total_hits'], -1);
        $this->assertEmpty($response['next_token']);
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
        for ($i = 0; $i < 5; $i++) {
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
                    array('geo', $i . ',' . $i),
                    array('long', $i),
                    array('double', $i + 0.1),
                    array('boolean', $i % 2 == 0),
                    array('array', '["search","index' . $i . '"]'),
                    array('nested', '[{"nested_keyword":"sub","nested_long":' . $i . '}]')
                )
            );

            SDKTestBase::putInitialData($request);
        }
    }
}

