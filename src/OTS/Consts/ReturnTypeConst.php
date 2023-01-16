<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述属性列改变类型RowChange的数据类型。 */
class ReturnTypeConst
{
    const CONST_NONE = 'NONE';
    const CONST_PK = 'PK';
    const CONST_AFTER_MODIFY = 'AFTER_MODIFY';

    public static function values()
    {
        return array(
            ReturnTypeConst::CONST_NONE,
            ReturnTypeConst::CONST_PK,
            ReturnTypeConst::CONST_AFTER_MODIFY,
        );
    }

    public static function members()
    {
        return array(
            'ReturnTypeConst::CONST_NONE',
            'ReturnTypeConst::CONST_PK',
            'ReturnTypeConst::CONST_AFTER_MODIFY'
        );
    }
}
