<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';


/*
 * 宽行读取测试，参见：
 * https://help.aliyun.com/document_detail/44573.html
 */
class WideRowTest extends SDKTestBase {

    private static $usedTables = array (
        'WideRowTable'
    );

    public static function setUpBeforeClass()
    {
        SDKTestBase::cleanUp (self::$usedTables);
        SDKTestBase::createInitialTable (array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_INTEGER),
                    array('PK3', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK4', PrimaryKeyTypeConst::CONST_INTEGER)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
            )
        ));
        SDKTestBase::waitForTableReady ();

        $attr = array();
        for($i = 1000; $i < 2024; $i++) {
            $attr[] = array('col' . $i, 'a'.$i);
        }

        SDKTestBase::putInitialData (array (
            'table_name' => self::$usedTables[0],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'a1'),
                array('PK2', 1),
                array('PK3', 'a11'),
                array('PK4', 11)
            ),
            'attribute_columns' => $attr
        ));
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp (self::$usedTables);
    }

    /*
     * GetRowWithStartEndColumn, 采用startColumn和EndColum来过滤属性
     */
    public function testGetRowWithStartEndColumn() {
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 'a1'),
                array('PK2', 1),
                array('PK3', 'a11'),
                array('PK4', 11)
            ),
            'max_versions' => 1,
            'start_column' => 'col1002',
            'end_column' => 'col1007',
        );
        $getrow = $this->otsClient->getRow ($body);
        $expectColumn = array(
            array('col1002', 'a1002'),
            array('col1003', 'a1003'),
            array('col1004', 'a1004'),
            array('col1005', 'a1005'),
            array('col1006', 'a1006')
        );
        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);
    }

    /*
     * GetColumnPagination, 采用columnPagination
     */
    public function testGetColumnPagination() {
        $body = array (
            'table_name' => self::$usedTables[0],
            'primary_key' => array (
                array('PK1', 'a1'),
                array('PK2', 1),
                array('PK3', 'a11'),
                array('PK4', 11)
            ),
            'max_versions' => 1,
            'start_column' => 'col1003',
            'column_filter' => array (
                'column_pagination' => array(
                    'offset' => 2,
                    'limit' => 5
                )
            )
        );
        $getrow = $this->otsClient->getRow ($body);
        $expectColumn = array(
            array('col1005', 'a1005'),
            array('col1006', 'a1006'),
            array('col1007', 'a1007'),
            array('col1008', 'a1008'),
            array('col1009', 'a1009')
        );
        $this->assertColumnEquals($expectColumn, $getrow['attribute_columns']);
    }

    /*
     * ReadWithToken, 一次性返回的过多，所以可以返回一个token,
     * 可以采用token继续读取。TODO:目前暂未成功获取到token，每次都能读全一行。
     */
    public function gtestReadWithToken() {

        for($m = 0; $m < 50; $m++) {
            $attr = array();
            for ($i = 0; $i < 1024; $i++) {
                $attr[] = array('col-' .$m. '-'. $i, str_repeat('value'.$i, 50));
            }
            $updateRow = array(
                'table_name' => self::$usedTables[0],
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => array(
                    array('PK1', 'a2'),
                    array('PK2', 2),
                    array('PK3', 'a22'),
                    array('PK4', 22)
                ),
                'attribute_columns_to_put' => $attr
            );
            $this->otsClient->updateRow($updateRow);
        }

        $body = array (
            'table_name' => self::$usedTables[0],
            'token' => '',
            'primary_key' => array (
                array('PK1', 'a2'),
                array('PK2', 2),
                array('PK3', 'a22'),
                array('PK4', 22)
            )
        );

        $this->otsClient->getClientConfig()->socketTimeout = 10;

        $getrow = $this->otsClient->getRow ($body);

    }
}

