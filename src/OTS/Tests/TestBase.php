<?php

namespace Aliyun\OTS\Tests;

include "TestConfig.php";

use Aliyun\OTS;

date_default_timezone_set ('Asia/Shanghai');

// require(__DIR__ . "/../../../vendor/autoload.php");
class SDKTestBase extends \PHPUnit_Framework_TestCase {
    protected $otsClient;
    public function __construct() {
        parent::__construct ();
        $this->otsClient = SDKTestBase::createOTSClient ();
    }
    public static function createOTSClient() {
        $sdkTestConfig = array (
                'EndPoint' => SDK_TEST_END_POINT,
                'AccessKeyID' => SDK_TEST_ACCESS_KEY_ID,
                'AccessKeySecret' => SDK_TEST_ACCESS_KEY_SECRET,
                'InstanceName' => SDK_TEST_INSTANCE_NAME
        );
        
        return new \Aliyun\OTS\OTSClient ($sdkTestConfig);
    }
    public function cleanUp(array $tables = null) {
        if ($tables != null) {
            $otsClient = SDKTestBase::createOTSClient ();
            $tableNames = $otsClient->listTable (array ());
            foreach ($tables as $tableName) {
                if (in_array ($tableName, $tableNames))
                    $otsClient->deleteTable (array (
                            'table_name' => $tableName
                    ));
            }
        } else {
            $otsClient = SDKTestBase::createOTSClient ();
            $tableNames = $otsClient->listTable (array ());
            foreach ($tableNames as $tableName) {
                $otsClient->deleteTable (array (
                        'table_name' => $tableName
                ));
            }
        }
    }
    public static function putInitialData(array $request) {
        $otsClient = SDKTestBase::createOTSClient ();
        $otsClient->putRow ($request);
    }
    public static function createInitialTable(array $request) {
        $otsClient = SDKTestBase::createOTSClient ();
        $otsClient->createTable ($request);
    }
    public static function waitForTableReady() {
        sleep (30);
    }
    public static function waitForCUAdjustmentInterval() {
        sleep (125);
    }
    public function tearDown() {
    }
}
