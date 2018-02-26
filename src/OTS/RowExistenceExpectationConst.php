<?php

namespace Aliyun\OTS;

/* 该类用于描述对于数据行是否存在的期望值。 */
class RowExistenceExpectationConst {
    const CONST_IGNORE = 'IGNORE';
    const CONST_EXPECT_EXIST = 'EXPECT_EXIST';
    const CONST_EXPECT_NOT_EXIST = 'EXPECT_NOT_EXIST';
    static public function values() {
        return array (
                RowExistenceExpectationConst::CONST_IGNORE,
                RowExistenceExpectationConst::CONST_EXPECT_EXIST,
                RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST 
        );
    }
    static public function members() {
        return array (
                'RowExistenceExpectationConst::CONST_IGNORE',
                'RowExistenceExpectationConst::CONST_EXPECT_EXIST',
                'RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST' 
        );
    }
}