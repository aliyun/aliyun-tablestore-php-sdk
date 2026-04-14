<?php

namespace Aliyun\OTS\Tests;

use Aliyun\OTS\OTSClient;
use Aliyun\OTS\OTSClientConfig;
use Aliyun\OTS\Handlers\OTSHandlers;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Unit tests for OTSClient that do not require a real TableStore connection.
 */
class OTSClientUnitTest extends TestCase
{
    private OTSClient $client;

    protected function setUp(): void
    {
        $this->client = new OTSClient([
            'EndPoint' => 'https://test-instance.cn-hangzhou.ots.aliyuncs.com',
            'AccessKeyID' => 'test-access-key-id',
            'AccessKeySecret' => 'test-access-key-secret',
            'InstanceName' => 'test-instance',
        ]);
    }

    public function testGetClientConfigReturnsOTSClientConfig(): void
    {
        $config = $this->client->getClientConfig();
        $this->assertInstanceOf(OTSClientConfig::class, $config);
    }

    public function testGetHandlersReturnsOTSHandlers(): void
    {
        $handlers = $this->client->getHandlers();
        $this->assertInstanceOf(OTSHandlers::class, $handlers);
    }

    public function testGetHandlersReturnsSameInstance(): void
    {
        $handlers1 = $this->client->getHandlers();
        $handlers2 = $this->client->getHandlers();
        $this->assertSame($handlers1, $handlers2, 'getHandlers() should return the same instance on multiple calls');
    }
}
