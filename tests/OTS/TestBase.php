<?php

namespace Aliyun\OTS\Tests;

include "TestConfig.php";

use Aliyun\OTS;

date_default_timezone_set ('Asia/Shanghai');

// require(__DIR__ . "/../../../vendor/autoload.php");
abstract class SDKTestBase extends \PHPUnit_Framework_TestCase {
    protected $otsClient;
    public function __construct() {
        parent::__construct ();
        $this->otsClient = SDKTestBase::createOTSClient ();
    }
    public static function createOTSClient() {
        $sdkTestConfig = array (
                'EndPoint' => SDK_TEST_END_POINT == '' ? getenv('SDK_TEST_END_POINT') : SDK_TEST_END_POINT,
                'AccessKeyID' => SDK_TEST_ACCESS_KEY_ID == '' ? getenv('SDK_TEST_ACCESS_KEY_ID') : SDK_TEST_ACCESS_KEY_ID,
                'AccessKeySecret' => SDK_TEST_ACCESS_KEY_SECRET == '' ? getenv('SDK_TEST_ACCESS_KEY_SECRET') : SDK_TEST_ACCESS_KEY_SECRET,
                'InstanceName' => SDK_TEST_INSTANCE_NAME == '' ? getenv('SDK_TEST_INSTANCE_NAME') : SDK_TEST_INSTANCE_NAME
        );
        
        return new \Aliyun\OTS\OTSClient ($sdkTestConfig);
    }
    public static function cleanUp(array $tables = null) {

        if ($tables != null) {
            $otsClient = SDKTestBase::createOTSClient ();
            $tableNames = $otsClient->listTable (array ());
            foreach ($tables as $tableName) {
                if (in_array ($tableName, $tableNames))
                    $otsClient->deleteTable (array (
                            'table_name' => $tableName
                    ));
                SDKTestBase::waitForAvoidFrequency();
            }
        } else {
            $otsClient = SDKTestBase::createOTSClient ();
            $tableNames = $otsClient->listTable (array ());
            foreach ($tableNames as $tableName) {
                $otsClient->deleteTable (array (
                        'table_name' => $tableName
                ));
                SDKTestBase::waitForAvoidFrequency();
            }
        }
    }
    public static function cleanUpSearchIndex($tableName) {
        $otsClient = SDKTestBase::createOTSClient ();
        $searchIndexes = $otsClient->listSearchIndex(array(
            'table_name' => $tableName
        ));
        foreach ($searchIndexes as $index) {
            $otsClient->deleteSearchIndex($index);
            SDKTestBase::waitForAvoidFrequency();
        }
    }
    public static function putInitialData(array $request) {
        $otsClient = SDKTestBase::createOTSClient ();
        $otsClient->putRow ($request);
    }
    public static function createInitialTable(array $request) {
        $otsClient = SDKTestBase::createOTSClient ();
        $otsClient->createTable ($request);
        SDKTestBase::waitForAvoidFrequency();
    }
    public static function createSearchIndex(array $request) {
        $otsClient = SDKTestBase::createOTSClient();
        $otsClient->createSearchIndex($request);
        SDKTestBase::waitForAvoidFrequency();
    }
    public static function createGlobalIndex(array $request) {
        $otsClient = SDKTestBase::createOTSClient();
        $otsClient->createIndex($request);
        SDKTestBase::waitForAvoidFrequency();
    }
    public static function cleanUpGlobalIndex($tableName) {
        $otsClient = SDKTestBase::createOTSClient();
        $indexes = $otsClient->describeTable(array('table_name' => $tableName));
        foreach($indexes['index_metas'] as $index) {
            $otsClient->dropIndex(array('table_name' => $tableName, 'index_name' => $index['name']));
        }
    }
    public static function waitForTableReady() {
        sleep (10);
    }
    public static function waitForAvoidFrequency() {
        sleep (1);
    }
    public static function waitForCUAdjustmentInterval() {
        sleep (125);
    }
    public static function waitForSearchIndexSync() {
        sleep (25);
    }
    public function tearDown() {
    }

    public function assertRowEquals($expect, $actual) {
        $this->assertTrue(is_array($expect));
        $this->assertTrue(is_array($actual));
        $this->assertEquals($expect['primary_key'], $actual['primary_key']);
        $this->assertColumnEquals($expect['attribute_columns'], $actual['attribute_columns']);
    }

    public function assertColumnEquals($expect, $actual) {
        $this->assertTrue(is_array($expect));
        $this->assertTrue(is_array($actual));
        $this->assertEquals(count($expect), count($actual));
        for($i = 0; $i < count($expect); $i++) {
            $this->assertEquals($expect[$i][0], $actual[$i][0]);
            if(!empty($expect[$i][1])) {
                $this->assertEquals($expect[$i][1], $actual[$i][1]);
            }
            if(!empty($expect[$i][2])) {
                $this->assertEquals($expect[$i][2], $actual[$i][2]);
            }
            if(!empty($expect[$i][3])) {
                $this->assertEquals($expect[$i][3], $actual[$i][3]);
            }
        }
    }
}
