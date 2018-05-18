<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述主键的数据类型。 */
class PrimaryKeyOptionConst {
    const CONST_PK_AUTO_INCR = 'PK_AUTO_INCR';
    public static function values() {
        return array (
            PrimaryKeyOptionConst::CONST_PK_AUTO_INCR
        );
    }
    public static function members() {
        return array (
                'PrimaryKeyOptionConst::CONST_PK_AUTO_INCR'
        );
    }
}
