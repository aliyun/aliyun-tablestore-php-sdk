<?php

namespace Aliyun\OTS;

/* 该类表示逻辑运算符。 */
class LogicalOperatorConst {
    const CONST_NOT = 1;
    const CONST_AND = 2;
    const CONST_OR = 3;
    static public function values() {
        return array (
                LogicalOperatorConst::CONST_AND,
                LogicalOperatorConst::CONST_OR,
                LogicalOperatorConst::CONST_NOT 
        );
    }
    static public function memebers() {
        return array (
                'LogicalOperatorConst::CONST_AND',
                'LogicalOperatorConst::CONST_OR',
                'LogicalOperatorConst::CONST_NOT' 
        );
    }
}