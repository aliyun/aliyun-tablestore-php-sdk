<?php

namespace Aliyun\OTS;

/* 该类被使用于描述主键和属性列的数据类型。 */
class ColumnTypeConst {
    const CONST_STRING = 'STRING';
    const CONST_INTEGER = 'INTEGER';
    const CONST_BOOLEAN = 'BOOLEAN';
    const CONST_DOUBLE = 'DOUBLE';
    const CONST_BINARY = 'BINARY';
    const CONST_INF_MIN = 'INF_MIN';
    const CONST_INF_MAX = 'INF_MAX';
    static public function values() {
        return array (
                ColumnTypeConst::CONST_BINARY,
                ColumnTypeConst::CONST_BOOLEAN,
                ColumnTypeConst::CONST_DOUBLE,
                ColumnTypeConst::CONST_INTEGER,
                ColumnTypeConst::CONST_STRING,
                ColumnTypeConst::CONST_INF_MAX,
                ColumnTypeConst::CONST_INF_MIN 
        );
    }
    static public function members() {
        return array (
                'ColumnTypeConst::CONST_BINARY',
                'ColumnTypeConst::CONST_BOOLEAN',
                'ColumnTypeConst::CONST_DOUBLE',
                'ColumnTypeConst::CONST_INTEGER',
                'ColumnTypeConst::CONST_STRING',
                'ColumnTypeConst::CONST_INF_MAX',
                'ColumnTypeConst::CONST_INF_MIN' 
        );
    }
}
