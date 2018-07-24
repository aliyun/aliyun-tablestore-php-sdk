<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\StreamStatusConst;

require_once __DIR__ . '/TestBase.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class StreamTest extends SDKTestBase {

    private static $usedTables = array (
        'tableForStream',
        'tableForStream2'
    );

    public static function setUpBeforeClass()
    {
        SDKTestBase::cleanUp (self::$usedTables);
    }

    public static function tearDownAfterClass()
    {
        SDKTestBase::cleanUp (self::$usedTables);
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
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
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
        $this->assertEquals (false, $table_meta['stream_details']['enable_stream']);

        $tablebody = array (
            'table_name' => self::$usedTables[0],
            'stream_spec' => array(
                'enable_stream' => true,
                'expiration_time' => 24
            )
        );
        $this->otsClient->updateTable ($tablebody);
        $table_meta = $this->otsClient->describeTable ($tablename);
        $this->assertEquals ($tablebody['stream_spec']['enable_stream'], $table_meta['stream_details']['enable_stream']);
        $this->assertEquals ($tablebody['stream_spec']['expiration_time'], $table_meta['stream_details']['expiration_time']);
    }

    /*
     * Stream
     * 测试Stream功能, ListStream, DescribeStream, GetShardIterator, GetStreamRecord。
     */
    public function testStream() {

        // 1. CreateTable without stream
        $tablebody = array (
            'table_meta' => array (
                'table_name' => self::$usedTables[1],
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
            'table_options' => array(
                'time_to_live' => -1,
                'max_versions' => 2,
                'deviation_cell_version_in_sec' => 86400
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

        // 2. listStream
        $streams = $this->otsClient->listStream($tablename);
        $this->assertEquals (0, count($streams['streams']));

        // 3. updateTable to enable Stream
        $tablebody = array (
            'table_name' => self::$usedTables[1],
            'stream_spec' => array(
                'enable_stream' => true,
                'expiration_time' => 24
            )
        );
        $this->otsClient->updateTable ($tablebody);

        // 4. just listStream again
        $streams = $this->otsClient->listStream($tablename);
        $this->assertEquals (1, count($streams['streams']));
        $this->assertEquals ($tablename['table_name'], $streams['streams'][0]['table_name']);

        $streamId = $streams['streams'][0]['stream_id'];

        // 5. try to describe stream by stream_id
        $streamRequst = array(
            'stream_id' => $streamId
        );

        $streamResponse = $this->otsClient->describeStream($streamRequst);
        $this->assertEquals ($streams['streams'][0]['stream_id'], $streamResponse['stream_id']);
        $this->assertEquals (24, $streamResponse['expiration_time']);
        $this->assertEquals ($tablename['table_name'], $streamResponse['table_name']);
        $this->assertEquals (StreamStatusConst::CONST_ACTIVE, $streamResponse['stream_status']);
        $this->assertEquals (1, count($streamResponse['shards']));
        $this->assertNotEmpty($streamResponse['shards'][0]['shard_id']);
        $this->assertEmpty($streamResponse['shards'][0]['parent_id']);
        $this->assertEmpty($streamResponse['shards'][0]['parent_sibling_id']);
        $this->assertEmpty($streamResponse['next_shard_id']);

        $shardId = $streamResponse['shards'][0]['shard_id'];

        // 6. try to get shard_iterator by stream_id and shard_id
        $iterRequst = array(
            'stream_id' => $streamId,
            'shard_id' => $shardId
        );
        $iterResponse = $this->otsClient->getShardIterator($iterRequst);
        $this->assertNotEmpty($iterResponse['shard_iterator']);

        $iter = $iterResponse['shard_iterator'];
        // 7. get_stream_record by shard_iterator
        $recordRequest = array(
            'shard_iterator' => $iter
        );

        $recordResponse = $this->otsClient->getStreamRecord($recordRequest);

        $iter = $recordResponse['next_shard_iterator'];

        // 8. PutRow to getStream.
        $row = array (
            'table_name' => self::$usedTables[1],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'test1'),
                array('PK2', 'test2')
            ),
            'attribute_columns' => array (
                array('colToDel', 'abc'),
                array('colToDelAll', true),
                array('colToUpdate', 1234)
            )
        );
        $this->otsClient->putRow ($row);
        $recordRequest = array(
            'shard_iterator' => $iter
        );

        $recordResponse = $this->otsClient->getStreamRecord($recordRequest);

        $iter = $recordResponse['next_shard_iterator'];
        $timestamp = $recordResponse['stream_records'][0]['attribute_columns'][0][3];

        // 9. UpdateRow to getStream
        $updaterow = array (
            'table_name' => self::$usedTables[1],
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => array (
                array('PK1', 'test1'),
                array('PK2', 'test2')
            ),
            'update_of_attribute_columns'=> array(
                'PUT' => array (
                    array('colToUpdate', 3.14)
                ),
                'DELETE' => array(
                    array('colToDel', $timestamp)
                ),
                'DELETE_ALL' => array(
                    'colToDelAll'
                )
            )
        );
        $this->otsClient->updateRow($updaterow);
        $recordRequest = array(
            'shard_iterator' => $iter
        );

        $recordResponse = $this->otsClient->getStreamRecord($recordRequest);
        $iter = $recordResponse['next_shard_iterator'];

        // 10. DeleteRow to getStream
        $deleterow = array (
            'table_name' => self::$usedTables[1],
            'condition' => RowExistenceExpectationConst::CONST_EXPECT_EXIST,
            'primary_key' => array (
                array('PK1', 'test1'),
                array('PK2', 'test2')
            )
        );

        $this->otsClient->deleteRow ($deleterow);

        $recordRequest = array(
            'shard_iterator' => $iter
        );

        $recordResponse = $this->otsClient->getStreamRecord($recordRequest);
    }


}
