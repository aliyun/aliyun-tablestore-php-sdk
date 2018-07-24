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

function getMicroTime()
{
    return ceil(microtime(true) * 1000);
}

/**
 * 工具函数。attribute_column默认是list类型。
 * 通过此函数可以转换成map的结构，方便通过columnName来获取columnValue.
 * @param $columns
 * @return array
 */
function getColumnValueAsMap($columns)
{
    $ret = array();
    foreach($columns as $column) {
        if(isset($ret[$column[0]])) {
            $ret[$column[0]][] = $column;
        } else {
            $ret[$column[0]] = [$column];
        }
    }
    return $ret;
}
