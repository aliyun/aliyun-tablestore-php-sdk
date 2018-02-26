<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS\RetryPolicy;

class RetryHandler
{
    public function handleBefore($context)
    {
        // empty
    }

    public function handleAfter($context)
    {
        $retryPolicy = $context->clientConfig->retryPolicy;

        if ($context->otsServerException == null) {
            $context->shouldRetry = false;
            $context->retryTimes = 0;
            return;
        }

        if ($retryPolicy->maxRetryTimeReached($context)) {
            $context->shouldRetry = false;
            return;
        }

        if (!$retryPolicy->canRetry($context)) {
            $context->shouldRetry = false;
            return;
        }

        $context->retryTimes += 1;
        $context->retryDelayInMilliSeconds = $retryPolicy->getRetryDelay($context);
        $context->shouldRetry = true;
    }
}
