<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;

class ErrorHandler
{
    private function logOTSServerException(RequestContext $context, \Aliyun\OTS\OTSServerException $exception)
    {
        $errorLogger = $context->clientConfig->errorLogHandler;

        if ($errorLogger != null) {
            $errorLogger((string)$exception);
        }
    }

    public function handleBefore(RequestContext $context)
    {
        // empty
    }

    public function handleAfter(RequestContext $context)
    {
        if ($context->responseHttpStatus >= 200 && $context->responseHttpStatus < 300) {
            return;
        }

        $error = new \Error();
        $errorCode = null;
        $errorMessage = null;

        try {
            $error->ParseFromString($context->responseBody);
            $errorCode = $error->code();
            $errorMessage = $error->message();
        } catch (\Exception $e) {

            // Sometimes the response body is not a valid Error PB Message,
            // in this case the user should get informed with http status
            $exception = new \Aliyun\OTS\OTSServerException($context->apiName, $context->responseHttpStatus);
            $this->logOTSServerException($context, $exception);
            $context->otsServerException = $exception;
            return;
        }

        $requestId = null;
        if (isset($context->responseHeaders['x-ots-requestid'])) {
            $requestId = $context->responseHeaders['x-ots-requestid'];
        }

        $exception = new \Aliyun\OTS\OTSServerException(
            $context->apiName, $context->responseHttpStatus,
            $errorCode, $errorMessage, $requestId);
        $this->logOTSServerException($context, $exception);
        $context->otsServerException = $exception;
    }
}

