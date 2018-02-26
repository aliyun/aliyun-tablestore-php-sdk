<?php 

namespace Aliyun\OTS\Retry;

use Aliyun\OTS\Handlers\RequestContext as RequestContext;

/**
 *
 * 不进行任何重试的重试策略
 *
 */
class NoRetryPolicy implements RetryPolicy
{
    public function maxRetryTimeReached(RequestContext $context)
    {
        return true;
    }

    public function canRetry(RequestContext $context)
    {
        return false;
    }
     
    public function getRetryDelay(RequestContext $context)
    {
        return 0;
    }
}

