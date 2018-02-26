<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\ColumnTypeConst;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\RowExistenceExpectationConst;

require __DIR__ . "/TestBase.php";
require __DIR__ . "/../../../vendor/autoload.php";

$usedTables = array (
    "myTable",
    "myTable1" 
);

SDKTestBase::cleanUp ( $usedTables );
SDKTestBase::createInitialTable ( array (
    "table_meta" => array (
        "table_name" => $usedTables[0],
        "primary_key_schema" => array (
            "PK1" => ColumnTypeConst::CONST_INTEGER,
            "PK2" => ColumnTypeConst::CONST_STRING 
        ) 
    ),
    "reserved_throughput" => array (
        "capacity_unit" => array (
            "read" => 0,
            "write" => 0 
        ) 
    ) 
) );
SDKTestBase::createInitialTable ( array (
    "table_meta" => array (
        "table_name" => $usedTables[1],
        "primary_key_schema" => array (
            "PK1" => ColumnTypeConst::CONST_INTEGER,
            "PK2" => ColumnTypeConst::CONST_STRING 
        ) 
    ),
    "reserved_throughput" => array (
        "capacity_unit" => array (
            "read" => 0,
            "write" => 0 
        ) 
    ) 
) );
SDKTestBase::waitForTableReady ();
class PutRowTest extends SDKTestBase {
    
    /*
     *
     * TableNameOfZeroLength
     * 表名长度为0的情况，期望返回错误消息：Invalid table name: '{TableName}'. 中包含的TableName与输入一致。
     */
    public function testTableNameOfZeroLength() {
        $tablename1 = array (
            "table_name" => "",
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1" 
            ),
            "attribute_columns" => array (
                "attr1" => "name",
                "attr2" => 256 
            ) 
        );
        try {
            $this->otsClient->putRow ( $tablename1 );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $c = "Invalid table name: ''.";
            $this->assertEquals ( $c, $exc->getOTSErrorMessage () );
        }
        // print_r($this->otsClient->putRow($tablename));
        // die;
        $tablename2 = array (
            "table_name" => "testU+0053",
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1" 
            ),
            "attribute_columns" => array (
                "attr1" => "name",
                "attr2" => 256 
            ) 
        );
        try {
            $this->otsClient->putRow ( $tablename2 );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $c = "Invalid table name: 'testU+0053'.";
            $this->assertEquals ( $c, $exc->getOTSErrorMessage () );
        }
    }
    
    /*
     * ColumnNameOfZeroLength
     * 列名长度为0的情况，期望返回错误消息：Invalid column name: '{ColumnName}'. 中包含的ColumnName与输入一致
     * ColumnNameWithUnicode
     * 列名包含Unicode，期望返回错误信息：Invalid column name: '{ColumnName}'. 中包含的ColumnName与输入一致。
     * 1KBColumnName
     * 列名包含Unicode，期望返回错误信息：Invalid column name: '{ColumnName}'. 中包含的ColumnName与输入一致。
     */
    public function testColumnNameLength() {
        global $usedTables;
        // ColumnNameOfZeroLength
        $tablename1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1" 
            ),
            "attribute_columns" => array (
                "" => "name",
                "attr2" => 256 
            ) 
        );
        try {
            $this->otsClient->putRow ( $tablename1 );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $c = "Invalid column name: ''.";
            $this->assertEquals ( $c, $exc->getOTSErrorMessage () );
        }
        // ColumnNameWithUnicode
        $tablename2 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1" 
            ),
            "attribute_columns" => array (
                "#name" => "name",
                "attr2" => 256 
            ) 
        );
        try {
            $this->otsClient->putRow ( $tablename2 );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $c = "Invalid column name: '#name'.";
            $this->assertEquals ( $c, $exc->getOTSErrorMessage () );
        }
        // 1KBColumnName
        $name = "";
        for($i = 1; $i < 1025; $i ++) {
            $name .= "a";
        }
        $tablename3 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1" 
            ),
            "attribute_columns" => array (
                "{$name}" => "name",
                "attr2" => 256 
            ) 
        );
        try {
            $this->otsClient->putRow ( $tablename3 );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $c = "Invalid column name: '{$name}'.";
            $this->assertEquals ( $c, $exc->getOTSErrorMessage () );
        }
    }
    
    /*
     * 10WriteCUConsumed
     * 测试接口消耗10个写CU时返回的CU Consumed符合预期。
     */
    public function testWrite10CUConsumed() {
        global $usedTables;
        $name = "";
        for($i = 1; $i < (4097 * 9); $i ++) {
            $name .= "a";
        }
        $tablename = array (
            "table_name" => $usedTables[1],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 1,
                "PK2" => "a1" 
            ),
            "attribute_columns" => array (
                "att2" => $name 
            ) 
        );
        
        if (is_array ( $this->otsClient->putRow ( $tablename ) )) {
            $name = $this->otsClient->putRow ( $tablename );
            $this->assertEquals ( $name['consumed']['capacity_unit']['write'], 10 );
            $this->assertEquals ( $name['consumed']['capacity_unit']['read'], 0 );
        }
        // print_r($name['consumed']['capacity_unit']['write']);die;
        // $getrow = $this->otsClient->putRow($tablename);
    }
    
    /*
     * 测试不同类型的列值
     * NormanStringValue
     * 测试StringValue为10个字节的情况。
     * UnicodeStringValue
     * 测试StringValue包含Unicode字符的情况。
     */
    public function testNormanStringValue() {
        global $usedTables;
        $name = "";
        for($i = 1; $i < (1025 * 10); $i ++) {
            $name .= "a";
        }
        // echo strlen($a);die;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 11,
                "PK2" => "a11" 
            ),
            "attribute_columns" => array (
                "att2" => $name 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                'PK1' => 11,
                'PK2' => 'a11' 
            ),
            "columns_to_get" => array () 
        );
        
        if (is_array ( $this->otsClient->getRow ( $body ) )) {
            $name = $this->otsClient->getRow ( $body );
            $this->assertEquals ( $name['row']['attribute_columns']['att2'], $tablename['attribute_columns']['att2'] );
        }
        // print_r($name);die;
        // $getrow = $this->otsClient->putRow($tablename);
        //
    }
    
    /*
     * 测试不同类型的列值
     * UnicodeStringValue
     * 测试StringValue包含Unicode字符的情况。
     * EmptyStringValue
     * 测试空字符串的情况。
     */
    public function testUnicodeStringValue() {
        global $usedTables;
        // echo strlen($a);die;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 12,
                "PK2" => "a12" 
            ),
            "attribute_columns" => array (
                "att1" => "sdfv\u597d",
                "att2" => "U+0053" 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                'PK1' => 12,
                'PK2' => 'a12' 
            ) 
        );
        
        if (is_array ( $this->otsClient->getRow ( $body ) )) {
            $name = $this->otsClient->getRow ( $body );
            // UnicodeStringValue
            $this->assertEquals ( $name['row']['attribute_columns'], $tablename['attribute_columns'] );
        }
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 13,
                "PK2" => "a13" 
            ),
            "attribute_columns" => array (
                "att1" => "",
                "att2" => "" 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                'PK1' => 13,
                'PK2' => 'a13' 
            ) 
        );
        
        if (is_array ( $this->otsClient->getRow ( $body ) )) {
            $name = $this->otsClient->getRow ( $body );
            // UnicodeStringValue
            $this->assertEmpty ( $name['row']['attribute_columns']['att1'] );
            $this->assertEmpty ( $name['row']['attribute_columns']['att2'] );
        }
        // print_r($name);die;
        // $getrow = $this->otsClient->putRow($tablename);
        //
    }
    
    /*
     * StringValueTooLong
     * 测试字符串长度为1MB的情况，期望返回错误消息 最长65536。
     */
    public function testStringValueTooLong() {
        global $usedTables;
        $name = "";
        for($i = 1; $i < (1025 * 1024 * 5); $i ++) {
            $name .= "a";
        }
        // echo strlen($a);die;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 20,
                "PK2" => "a20" 
            ),
            "attribute_columns" => array (
                "att1" => $name 
            ) 
        );
        try {
            $this->otsClient->putRow ( $tablename );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $c = "The length of attribute column: 'att1' exceeded the MaxLength:";
            $this->assertContains ( $c, $exc->getOTSErrorMessage () );
        }
    }
    
    /*
     * CASE_ID: NormalIntegerValue
     * 测试IntegerValue值为10的情况。
     * CASE_ID: IntegerValueInBoundary
     * 测试IntegerValue的值为8位有符号整数的最小值或最大值的情况
     * 负8位整数getRow获取的值为4293856185
     * 正8位整数最大4293856185最下为0
     */
    public function testIntegerValue() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 30,
                "PK2" => "a30" 
            ),
            "attribute_columns" => array (
                "attr10" => - 10 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 30,
                "PK2" => "a30" 
            ),
            "columns_to_get" => array () 
        );
        $getrow = $this->otsClient->getRow ( $body );
        $this->assertEquals ( $getrow['row']['attribute_columns']['attr10'], - 10 );
        
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 31,
                "PK2" => "a31" 
            ),
            "attribute_columns" => array (
                "attr1" => 1,
                "attr2" => 0,
                "attr3" => 4293856185 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 31,
                "PK2" => "a31" 
            ),
            "columns_to_get" => array () 
        );
        $getrow1 = $this->otsClient->getRow ( $body );
        // echo $getrow['row']['attribute_columns']['attr1'];die;
        $this->assertEquals ( $getrow1['row']['attribute_columns']['attr1'], 1 );
        $this->assertEquals ( $getrow1['row']['attribute_columns']['attr2'], 0 );
        $this->assertEquals ( $getrow1['row']['attribute_columns']['attr3'], 4293856185 );
    }
    
    /*
     * NormalDoubleValue
     * 测试DoubleValue值为3.1415926的情况。
     * DoubleValueInBoundary
     * 测试DoubleValue的值为8位有符号浮点数的最小值或最大值的情况
     */
    public function testDoubleValue() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 40,
                "PK2" => "a40" 
            ),
            "attribute_columns" => array (
                "attr10" => 3.1415926 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 40,
                "PK2" => "a40" 
            ),
            "columns_to_get" => array () 
        );
        $getrow = $this->otsClient->getRow ( $body );
        $this->assertEquals ( $getrow['row']['attribute_columns']['attr10'], 3.1415926 );
        
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 41,
                "PK2" => "a41" 
            ),
            "attribute_columns" => array (
                "attr11" => - 0.0000001,
                "attr12" => 9.9999999 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 41,
                "PK2" => "a41" 
            ),
            "columns_to_get" => array () 
        );
        $getrow1 = $this->otsClient->getRow ( $body );
        // echo $getrow1['row']['attribute_columns']['attr11'];die;
        $this->assertEquals ( $getrow1['row']['attribute_columns']['attr11'], - 0.0000001 );
        // $this->assertEquals($getrow1['row']['attribute_columns']['attr2'],0);
        $this->assertEquals ( $getrow1['row']['attribute_columns']['attr12'], 9.9999999 );
    }
    
    /*
     * BooleanValueTrue
     * 描述：测试布尔值为True的情况。
     * BooleanValueFalse
     * 描述：测试布尔值为False的情况
     */
    public function testBooleanValue() {
        global $usedTables;
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "attribute_columns" => array (
                "attr1" => true,
                "attr2" => false 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "columns_to_get" => array () 
        );
        $getrow = $this->otsClient->getRow ( $body );
        $this->assertEquals ( $getrow['row']['attribute_columns']['attr1'], 1 );
        $this->assertEquals ( $getrow['row']['attribute_columns']['attr2'], 0 );
    }
    
    /*
     * ExpectNotExistConditionWhenRowNotExist
     * 描述：测试行不存在的条件下，写操作的Condition为EXPECT_NOT_EXIST。
     * ExpectNotExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_NOT_EXIST,返回错误信息Condition check failed.
     */
    public function testExpectNotExistConditionWhenRowNotExist() {
        global $usedTables;
        $request = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ) 
        );
        $this->otsClient->deleteRow ( $request );
        
        $tablename = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "attribute_columns" => array (
                "attr1" => true,
                "attr2" => false 
            ) 
        );
        $this->otsClient->putRow ( $tablename );
        $body = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "columns_to_get" => array () 
        );
        $getrow = $this->otsClient->getRow ( $body );
        $this->assertEquals ( $getrow['row']['attribute_columns']['attr1'], 1 );
        $this->assertEquals ( $getrow['row']['attribute_columns']['attr2'], 0 );
        
        $tablename1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "attribute_columns" => array (
                "attr1" => true,
                "attr2" => false 
            ) 
        );
        
        try {
            $this->otsClient->putRow ( $tablename1 );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $a = $exc->getMessage ();
            $c = "Condition check failed.";
            $this->assertContains ( $c, $a );
        }
    }
    
    /**
     * 测试在使用ColumnCondition的过滤条件的时候，插入数据是否成功。
     */
    public function testPutRowWithColumnCondition() {
        global $usedTables;
        $delete_query = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_IGNORE,
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ) 
        );
        $this->otsClient->deleteRow ( $delete_query );
        
        $put_query1 = array (
            "table_name" => $usedTables[0],
            "condition" => RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST,
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "attribute_columns" => array (
                "attr1" => true,
                "attr2" => true 
            ) 
        );
        $this->otsClient->putRow ( $put_query1 );
        
        $put_query2 = array (
            "table_name" => $usedTables[0],
            "condition" => array (
                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                "column_filter" => array (
                    "column_name" => "attr1",
                    "value" => true,
                    "comparator" => ComparatorTypeConst::CONST_EQUAL 
                ) 
            ),
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "attribute_columns" => array (
                "attr3" => false,
                "attr4" => false 
            ) 
        );
        $this->otsClient->putRow ( $put_query2 );
        
        $get_query = array (
            "table_name" => $usedTables[0],
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "columns_to_get" => array (
                "attr1",
                "attr2",
                "attr3",
                "attr4" 
            ) 
        );
        $get_row_res = $this->otsClient->getRow ( $get_query );
        $this->assertEquals ( $get_row_res['row']['attribute_columns']['attr3'], false );
        $this->assertEquals ( $get_row_res['row']['attribute_columns']['attr4'], false );
        
        $put_query3 = array (
            "table_name" => $usedTables[0],
            "condition" => array (
                "row_existence" => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                "column_filter" => array (
                    "column_name" => "attr3",
                    "value" => true,
                    "comparator" => ComparatorTypeConst::CONST_EQUAL 
                ) 
            ),
            "primary_key" => array (
                "PK1" => 50,
                "PK2" => "a50" 
            ),
            "attribute_columns" => array (
                "attr5" => false,
                "attr6" => false 
            ) 
        );
        try {
            $this->otsClient->putRow ( $put_query3 );
            $this->fail ( 'An expected exception has not been raised.' );
        } catch ( \Aliyun\OTS\OTSServerException $exc ) {
            $a = $exc->getMessage ();
            $c = "Condition check failed.";
            $this->assertContains ( $c, $a );
        }
    }
}

