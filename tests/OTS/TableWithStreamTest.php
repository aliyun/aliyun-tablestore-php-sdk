<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class TableWithStreamTest extends SDKTestBase {

    private static $usedTables = array (
        'tableStream'
    );


    public function setup()
    {
        $this->cleanUp (self::$usedTables);
    }

    public function tearDown()
    {
        $this->cleanUp (self::$usedTables);
    }

    /*
     * CreateTableWithStreamSpec
     * 测试CreateTable增加stream配置的场景。
     */
    public function testCreateTableWithStreamSpec() {
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[0],
                'primary_key_schema' => array (
                    array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                    array('PK2', PrimaryKeyTypeConst::CONST_STRING)
                )
            ),
            'reserved_throughput' => array (
                'capacity_unit' => array (
                    'read' => 0,
                    'write' => 0
                )
            ),
            'stream_spec' => array(
                'enable_stream' => true,
                'expiration_time' => 24
            )
        );
        $this->assertEmpty ($this->otsClient->createTable ($tablebody));
        SDKTestBase::waitForAvoidFrequency();
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array (
            'table_name' => $tablebody['table_meta']['table_name'],
            'primary_key_schema' => array (
                array('PK1', PrimaryKeyTypeConst::CONST_STRING),
                array('PK2', PrimaryKeyTypeConst::CONST_STRING)
            )
        );
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($tablebody['stream_spec']['enable_stream'], $table_meta['stream_details']['enable_stream']);
        $this->assertEquals ($tablebody['stream_spec']['expiration_time'], $table_meta['stream_details']['expiration_time']);

        $tablebody = array (
            'table_name' => self::$usedTables[0],
            'stream_spec' => array(
                'enable_stream' => false
            )
        );
        $this->otsClient->updateTable ($tablebody);
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($tablebody['stream_spec']['enable_stream'], $table_meta['stream_details']['enable_stream']);
    }
}
