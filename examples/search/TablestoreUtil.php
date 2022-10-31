<?php

use Aliyun\OTS\OTSClient;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\FieldTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\ColumnTypeConst;

class TablestoreUtil
{
    private $tableName;
    private $indexName;
    private $client;

    public function __construct(OTSClient $client, $tableName, $indexName)
    {
        $this->client = $client;
        $this->tableName = $tableName;
        $this->indexName = $indexName;
    }
        public function deleteTable($tableName)
    {
        $this->client->deleteTable(array("table_name" => $this->tableName));
    }

    public function deleteSearchIndex()
    {
        $this->client->deleteSearchIndex(array(
            "table_name" => $this->tableName,
            "index_name" => $this->indexName
        ));
    }

    public function deleteSearchIndexReindex()
    {
        $this->client->deleteSearchIndex(array(
            "table_name" => $this->tableName,
            "index_name" => $this->indexName . "_reindex"
        ));
    }

    public function createTable()
    {
        $request = array(
            'table_meta' => array(
                'table_name' => $this->tableName,
                'primary_key_schema' => array(
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER), // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING)
                )
            ), // 第二个主键列名称为PK1, 类型为STRING
            'reserved_throughput' => array(
                'capacity_unit' => array(
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
        $this->client->createTable($request);
    }

    public function createSearchIndex()
    {
        $request = array(
            'table_name' => $this->tableName,
            'index_name' => $this->indexName,
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
                                'index' => false,
                                'enable_sort_and_agg' => false,
                                'store' => false,
                                'is_array' => false
                            )
                        )
                    ),
                )
            )
        );

        $this->client->createSearchIndex($request);
    }

    public function putSomeData($count)
    {
        for ($i = 0; $i < $count; $i++) {
            $request = array (
                'table_name' => $this->tableName,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE, // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
                'primary_key' => array ( // 主键
                    array('PK0', $i),
                    array('PK1', 'abc')
                ),
                'attribute_columns' => array(
                    array('keyword', '你好' . $i),
                    array('text', '阿里云计算' . $i),
                    array('geo', '5.' . $i .",6." . $i),
                    array('long', $i % 10),
                    array('double', $i * 0.1),
                    array('boolean', $i % 3 == 0),
                    array('array', '["好","很好","' . $i . '"]'),
                    array('nested', '[{"nested_keyword":"好"},{"nested_keyword":"' . $i . '"}]')
                )
            );

            $this->client->putRow($request);
        }
    }
}
