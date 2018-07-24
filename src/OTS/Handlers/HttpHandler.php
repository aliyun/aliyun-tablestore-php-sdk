<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;
use Aliyun\OTS\OTSClientException;
use GuzzleHttp\Exception\TransferException;

class HttpHandler
{
    public function handleBefore($context)
    {
        $uri = "/" . $context->apiName;

        try {
            $httpResponse = $context->httpClient->request('POST', $uri, array(
                'body' => $context->requestBody,
                'headers' => $context->requestHeaders,
                'timeout' => $context->clientConfig->socketTimeout,
                'http_errors' => false, // don't throw exception when HTTP protocol errors are encountered
            ));
            $context->responseHeaders = array();
            $headers = $httpResponse->getHeaders();
            foreach ($headers as $key => $value) {
                $context->responseHeaders[$key] = $value[0];
            }

            $context->responseBody = (string)$httpResponse->getBody();
            $context->responseHttpStatus = $httpResponse->getStatusCode();
            $context->responseReasonPhrase = $httpResponse->getReasonPhrase();
        }
        catch (TransferException $e)
        {
            $otsClientException = new OTSClientException($e->getMessage());
            $this->logOTSClientException($context, $otsClientException);
            throw $otsClientException;
        }
    }

    private function logOTSClientException(RequestContext $context, OTSClientException $exception)
    {
        $errorLogger = $context->clientConfig->errorLogHandler;

        if ($errorLogger != null) {
            $errorLogger((string)$exception);
        }
    }


    public function handleAfter($context)
    {
        // empty
    }
}
