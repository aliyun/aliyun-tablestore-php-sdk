<?php

/**
 * 默认的Error级别日志函数，用来打印OTS服务端返回错误时的日志
 */
function defaultOTSErrorLogHandler($message) 
{
    $dateStr = date('Y-m-d H:i:s', time());
    print "OTS ERROR $dateStr $message\n";
}

/**
 * 默认的Debug级别日志函数，用来打印正常的请求和响应信息
 */
function defaultOTSDebugLogHandler($message) 
{
    $dateStr = date('Y-m-d H:i:s', time());
    print "OTS DEBUG $dateStr $message\n";
}

