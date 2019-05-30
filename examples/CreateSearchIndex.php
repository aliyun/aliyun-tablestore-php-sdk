<?php
require (__DIR__ . '/../vendor/autoload.php');
require (__DIR__ . '/ExampleConfig.php');

use Aliyun\OTS\OTSClient as OTSClient;

use Aliyun\OTS\Consts\FieldTypeConst;

$otsClient = new OTSClient(array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME,
));


$response = $otsClient->createSearchIndex(array(
    'table_name' => 'php_sdk_test',
    'index_name' => 'test_create_search_index',
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
        ),
        'index_setting' => array(
            'routing_fields' => array("pk1")
        ),
//        "index_sort" => array(
//            array(
//                'field_sort' => array(
//                    'field_name' => 'keyword',
//                    'order' => SortOrderConst::SORT_ORDER_ASC,
//                    'mode' => SortModeConst::SORT_MODE_AVG,
//                )
//            ),
//            array(
//                'pk_sort' => array(
//                    'order' => SortOrderConst::SORT_ORDER_ASC
//                )
//            ),
//        )
    )
));

print json_encode($response, JSON_PRETTY_PRINT);
