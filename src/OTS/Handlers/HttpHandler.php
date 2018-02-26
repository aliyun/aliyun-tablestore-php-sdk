<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;

class HttpHandler 
{
    public function handleBefore($context)
    {
        $uri = "/" . $context->apiName;

        if (method_exists($context->httpClient, "request")) {
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

        } else {
            // Be compatible with Guzzle older version

            $httpRequest = $context->httpClient->post($uri,
                $context->requestHeaders,
                $context->requestBody,
                array('timeout' => $context->clientConfig->socketTimeout));

            try {
                $httpResponse = $httpRequest->send();
            } catch (\Guzzle\Http\Exception\RequestException $e) {
                $httpResponse = $httpRequest->getResponse();
                if ($httpResponse == NULL) {
                    throw $e;
                }
            }
          
            $context->responseHeaders = array();
            foreach ($httpResponse->getHeaders()->toArray() as $key => $value) {
                $context->responseHeaders[$key] = $value[0];
            }
            $context->responseBody = $httpResponse->getBody(true);
            $context->responseHttpStatus = $httpResponse->getStatusCode();
            $context->responseReasonPhrase = $httpResponse->getReasonPhrase();
        }
    }

    public function handleAfter($context)
    {
        // empty
    }
}
