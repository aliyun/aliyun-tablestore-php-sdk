<?php

namespace Aliyun\OTS\Consts;

/* 该类用于描述数据查询的返回结果的排列方式。 */
class DirectionConst {
    const CONST_FORWARD = 'FORWARD';
    const CONST_BACKWARD = 'BACKWARD';
    public static function values() {
        return array (
                DirectionConst::CONST_FORWARD,
                DirectionConst::CONST_BACKWARD 
        );
    }
    public static function memebers() {
        return array (
                'DirectionConst::CONST_FORWARD',
                'DirectionConst::CONST_BACKWARD' 
        );
    }
}