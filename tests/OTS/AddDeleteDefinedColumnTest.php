<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\Consts\DefinedColumnTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class AddDeleteDefinedColumnTest extends SDKTestBase
{
    private static $tableName = 'testDefinedColumnTable';

    public static function setUpBeforeClass(): void
    {
        SDKTestBase::cleanUp(array(self::$tableName));
        SDKTestBase::createInitialTable(array(
            'table_meta' => array(
                'table_name' => self::$tableName,
                'primary_key_schema' => array(
                    array('PK0', PrimaryKeyTypeConst::CONST_INTEGER),
                ),
            ),
            'reserved_throughput' => array(
                'capacity_unit' => array('read' => 0, 'write' => 0),
            ),
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 1,
                'deviation_cell_version_in_sec' => 86400,
            ),
        ));
    }

    public static function tearDownAfterClass(): void
    {
        SDKTestBase::cleanUp(array(self::$tableName));
    }

    public function testAddAndDeleteDefinedColumn()
    {
        // Add 3 columns of different types
        $this->otsClient->addDefinedColumn(array(
            'table_name' => self::$tableName,
            'columns' => array(
                array('col_str', DefinedColumnTypeConst::DCT_STRING),
                array('col_int', DefinedColumnTypeConst::DCT_INTEGER),
                array('col_bool', DefinedColumnTypeConst::DCT_BOOLEAN),
            ),
        ));

        // Verify via DescribeTable
        $desc = $this->otsClient->describeTable(array('table_name' => self::$tableName));
        $columnNames = array();
        if (!empty($desc['table_meta']['defined_column'])) {
            foreach ($desc['table_meta']['defined_column'] as $col) {
                $columnNames[] = $col[0];
            }
        }
        $this->assertContains('col_str', $columnNames);
        $this->assertContains('col_int', $columnNames);
        $this->assertContains('col_bool', $columnNames);

        // Delete 2 columns
        $this->otsClient->deleteDefinedColumn(array(
            'table_name' => self::$tableName,
            'columns' => array('col_str', 'col_int'),
        ));

        $desc = $this->otsClient->describeTable(array('table_name' => self::$tableName));
        $columnNames = array();
        if (!empty($desc['table_meta']['defined_column'])) {
            foreach ($desc['table_meta']['defined_column'] as $col) {
                $columnNames[] = $col[0];
            }
        }
        $this->assertNotContains('col_str', $columnNames);
        $this->assertNotContains('col_int', $columnNames);
        $this->assertContains('col_bool', $columnNames);
    }
}
