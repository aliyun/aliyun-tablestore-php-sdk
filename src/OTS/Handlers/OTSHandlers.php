<?php

namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;


class OTSHandlers
{
    /** var OTSClientConfig */
    private $clientConfig;

    /** var GuzzleHttp\Client */
    private $httpClient;

    /** var RetryHandler */
    private $retryHandler;

    /** var ProtoBufferDecoder */
    private $protoBufferDecoder;

    /** var ProtoBufferEncoder */
    private $protoBufferEncoder;

    /** var ErrorHandler */
    private $errorHandler;

    /** var HttpHeaderHandler */
    private $httpHeaderHandler;

    /** var HttpHandler */
    private $httpHandler;

    public function __construct(\Aliyun\OTS\OTSClientConfig $config)
    {
        $this->clientConfig = $config;
        $this->retryHandler = new RetryHandler();
        $this->protoBufferDecoder = new ProtoBufferDecoder();
        $this->protoBufferEncoder = new ProtoBufferEncoder();
        $this->errorHandler = new ErrorHandler();
        $this->httpHeaderHandler = new HttpHeaderHandler();
        $this->httpHandler = new HttpHandler();

        $this->httpClient = new \GuzzleHttp\Client(array(
            'base_uri' => $config->getEndPoint(),
            'timeout' => $config->connectionTimeout,
        ));
    }

    public function doHandle($apiName, array $request) 
    {
        $context = new RequestContext($this->clientConfig, $this->httpClient, $apiName, $request);

        while (true) {
            $this->retryHandler->handleBefore($context);
            $this->protoBufferDecoder->handleBefore($context);
            $this->protoBufferEncoder->handleBefore($context);
            $this->errorHandler->handleBefore($context);
            $this->httpHeaderHandler->handleBefore($context);
            $this->httpHandler->handleBefore($context);

            $this->httpHandler->handleAfter($context);
            $this->httpHeaderHandler->handleAfter($context);
            $this->errorHandler->handleAfter($context);
            $this->protoBufferEncoder->handleAfter($context);
            $this->protoBufferDecoder->handleAfter($context);
            $this->retryHandler->handleAfter($context);

            if ($context->otsServerException != null) {
                if ($context->shouldRetry) {
                    usleep($context->retryDelayInMilliSeconds * 1000);
                    $context->otsServerException = null;
                    continue;
                } else {
                    throw $context->otsServerException;
                }
            } else {
                break;
            }
        }

        return $context->response;
    }
}
