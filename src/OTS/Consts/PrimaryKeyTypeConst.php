<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述主键的数据类型。 */
class PrimaryKeyTypeConst {
    const CONST_STRING = 'STRING';
    const CONST_INTEGER = 'INTEGER';
    const CONST_BINARY = 'BINARY';
    const CONST_INF_MIN = 'INF_MIN';
    const CONST_INF_MAX = 'INF_MAX';
    const CONST_PK_AUTO_INCR = 'PK_AUTO_INCR';
    public static function values() {
        return array (
            PrimaryKeyTypeConst::CONST_BINARY,
            PrimaryKeyTypeConst::CONST_INTEGER,
            PrimaryKeyTypeConst::CONST_STRING,
            PrimaryKeyTypeConst::CONST_INF_MIN,
            PrimaryKeyTypeConst::CONST_INF_MAX,
            PrimaryKeyTypeConst::CONST_PK_AUTO_INCR
        );
    }
    public static function members() {
        return array (
                'PrimaryKeyTypeConst::CONST_BINARY',
                'PrimaryKeyTypeConst::CONST_INTEGER',
                'PrimaryKeyTypeConst::CONST_STRING',
                'PrimaryKeyTypeConst::CONST_INF_MIN',
                'PrimaryKeyTypeConst::CONST_INF_MAX',
                'PrimaryKeyTypeConst::CONST_PK_AUTO_INCR'
        );
    }
}
