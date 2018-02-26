<?php

namespace Aliyun\OTS\Retry;

use Aliyun\OTS\Handlers\RequestContext as RequestConext;
 
class RetryUtil
{
    public static function shouldRetryNoMatterWhichAPI(RequestConext $context)
    {
        $exception = $context->otsServerException;

        if ($exception != null) 
        {
            $errorCode = $exception->getOTSErrorCode();
            $errorMessage = $exception->getOTSErrorMessage();

            if ($errorCode == "OTSRowOperationConflict" ||
                $errorCode == "OTSNotEnoughCapacityUnit" ||
                $errorCode == "OTSTableNotReady" ||
                $errorCode == "OTSPartitionUnavailable" ||
                $errorCode == "OTSServerBusy" ||
                $errorCode == "OTSOperationThrottled") {
                return true;
            }
             
            if ($errorCode == "OTSQuotaExhausted" &&
                $errorMessage == "Too frequent table operations.") {
                return true;
            }
        }
         
        return false;
    }
     
    public static function isRepeatableAPI($apiName)
    {
        if ($apiName == "ListTable" ||
            $apiName == "DescribeTable" ||
            $apiName == "GetRow" ||
            $apiName == "BatchGetRow" ||
            $apiName == "GetRange" || 
            $apiName == "DescrieStream" ||
            $apiName == "GetShardIterator" ||
            $apiName == "GetStreamRecord" ||
            $apiName == "ListStream") {
            return true;
        }
         
        return false;
    }
     
    public static function shouldRetryWhenAPIRepeatable(RequestConext $context)
    {
        $exception = $context->otsServerException;

        if ($exception != null) 
        {
            $errorCode = $exception->getOTSErrorCode();
            $errorMessage = $exception->getOTSErrorMessage();

            if ($errorCode == "OTSTimeout" ||
                $errorCode == "OTSInternalServerError" ||
                $errorCode == "OTSServerUnavailable") {
                return true;
            }
             
            $code = $context->responseHttpStatus;
            if ($code == 500 || $code == 502 || $code == 503)
            {
                return true;
            }
             
            // TODO handle network error & timeout
        }
         
        return false;
    }
     
    public static function isServerThrottlingException(RequestConext $context)
    {
        $exception = $context->otsServerException;

        if ($exception != null) 
        {
            $errorCode = $exception->getOTSErrorCode();
            $errorMessage = $exception->getOTSErrorMessage();

            if ($errorCode == "OTSServerBusy" ||
                $errorCode == "OTSNotEnoughCapacityUnit" ||
                $errorCode == "OTSOperationThrottled" ||
                ($errorCode == "OTSQuotaExhausted" && $errorMessage == "Too frequent table operations."))
            {
                return true;
            }
        }
         
        return false;
    }
}

