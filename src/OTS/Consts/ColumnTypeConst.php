<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述属性列的数据类型。 */
class ColumnTypeConst
{
    const CONST_STRING = 'STRING';
    const CONST_INTEGER = 'INTEGER';
    const CONST_BOOLEAN = 'BOOLEAN';
    const CONST_DOUBLE = 'DOUBLE';
    const CONST_BINARY = 'BINARY';

    public static function values()
    {
        return array(
            ColumnTypeConst::CONST_BINARY,
            ColumnTypeConst::CONST_INTEGER,
            ColumnTypeConst::CONST_BOOLEAN,
            ColumnTypeConst::CONST_DOUBLE,
            ColumnTypeConst::CONST_STRING
        );
    }

    public static function members()
    {
        return array(
            'ColumnTypeConst::CONST_BINARY',
            'ColumnTypeConst::CONST_INTEGER',
            'ColumnTypeConst::CONST_BOOLEAN',
            'ColumnTypeConst::CONST_DOUBLE',
            'ColumnTypeConst::CONST_STRING'
        );
    }
}
