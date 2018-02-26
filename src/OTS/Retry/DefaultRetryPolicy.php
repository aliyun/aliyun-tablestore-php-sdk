<?php 

namespace Aliyun\OTS\Retry;

use Aliyun\OTS\Handlers\RequestContext as RequestContext;

/**
 *
 * 默认重试策略
 * 最大重试次数为3，最大重试间隔为2000毫秒，对流控类错误以及读操作相关的服务端内部错误进行了重试。
 *
 */
class DefaultRetryPolicy implements RetryPolicy
{
    public $maxRetryTimes = 3;
     
    private $maxDelay = 2000;
     
    private $scaleFactor = 2;
     
    private $serverThrottlingExceptionDelayFactor = 500;
     
    private $stabilityExceptionDelayFactor = 200;
     
    public function __construct(int $maxRetryTimes = null, int $maxRetryDelay = null)
    {
        if ($maxRetryTimes != null) {
            $this->maxRetryTimes = $maxRetryTimes;
        }

        if ($maxRetryTimes < 0) {
            throw new OTSClientException("maxRetryTimes must be >= 0.");
        }

        if ($maxRetryDelay != null) {
            $this->maxDelay = $maxRetryDelay;
        }

        if ($maxRetryDelay < 0) {
            throw new OTSClientException("maxRetryDelay must be >= 0.");
        }
    }
     
    public function maxRetryTimeReached(RequestContext $context)
    {
        return $context->retryTimes >= $this->maxRetryTimes;
    }

    public function canRetry(RequestContext $context)
    {
         
        if (RetryUtil::shouldRetryNoMatterWhichAPI($context))
        {
            return true;
        }
         
        if (RetryUtil::isRepeatableAPI($context->apiName) &&
            RetryUtil::shouldRetryWhenAPIRepeatable($context))
        {
            return true;
        }
         
        return false;
    }
     
    public function getRetryDelay(RequestContext $context)
    {
        if (RetryUtil::isServerThrottlingException($context)) {
            $delayFactor = $this->serverThrottlingExceptionDelayFactor;
        } else {
            $delayFactor = $this->StabilityExceptionDelayFactor;
        }
         
        $delayLimit = $delayFactor * pow($this->scaleFactor, $context->retryTimes);
         
        if ($delayLimit >= $this->maxDelay) {
            $delayLimit = $this->maxDelay;
        }
         
         $realDelay = rand($delayLimit / 2, $delayLimit);
         
        return $realDelay;
    }
}

