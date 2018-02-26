<?php

namespace Aliyun\OTS;

/* 该类表示比较运算符。 */
class ComparatorTypeConst {
    const CONST_EQUAL = 1;
    const CONST_NOT_EQUAL = 2;
    const CONST_GREATER_THAN = 3;
    const CONST_GREATER_EQUAL = 4;
    const CONST_LESS_THAN = 5;
    const CONST_LESS_EQUAL = 6;
    static public function values() {
        return array (
                ComparatorTypeConst::CONST_EQUAL,
                ComparatorTypeConst::CONST_NOT_EQUAL,
                ComparatorTypeConst::CONST_LESS_THAN,
                ComparatorTypeConst::CONST_LESS_EQUAL,
                ComparatorTypeConst::CONST_GREATER_THAN,
                ComparatorTypeConst::CONST_GREATER_EQUAL 
        );
    }
    static public function memebers() {
        return array (
                'ComparatorTypeConst::CONST_EQUAL',
                'ComparatorTypeConst::CONST_NOT_EQUAL',
                'ComparatorTypeConst::CONST_LESS_THAN',
                'ComparatorTypeConst::CONST_LESS_EQUAL',
                'ComparatorTypeConst::CONST_GREATER_THAN',
                'ComparatorTypeConst::CONST_GREATER_EQUAL' 
        );
    }
}