<?php

namespace Aliyun\OTS\Retry;

use Aliyun\OTS\Handlers\RequestContext as RequestContext;

/**
 *
 * 重试逻辑的接口，规定了一个重试逻辑的最大重试次数，什么情况下进行重试，以及重试间隔。
 * 如果需要自定义重试策略，你需要定义一个类实现这个接口。
 * 请参考 DefaultRetryPolicy 的代码。
 *
 */
interface RetryPolicy
{
    public function maxRetryTimeReached(RequestContext $context);
    public function canRetry(RequestContext $context);
    public function getRetryDelay(RequestContext $context);
}
