<?php
namespace Aliyun\OTS\Handlers;
use Aliyun\OTS;
use GuzzleHttp\Client;

class RequestContext
{
    public $apiName;

    public $request;
    public $requestHeaders;
    public $requestBody;

    public $response;
    public $responseHeaders;
    public $responseHttpStatus;
    public $responseBody;
    public $otsServerException;

    public $clientConfig;
    public $httpClient;

    public $shouldRetry;
    public $retryDelayInMilliSeconds;
    public $retryTimes;

    public function __construct(
        \Aliyun\OTS\OTSClientConfig $clientConfig, 
        $httpClient, 
        $apiName, $request)
    {
        $this->apiName = $apiName;
        $this->request = $request;
        $this->clientConfig = $clientConfig;
        $this->httpClient = $httpClient;
        $this->otsServerException = null;
        $this->shouldRetry = false;
        $this->retryTimes = 0;
    }
}

