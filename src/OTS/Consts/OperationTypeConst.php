<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述属性列的数据类型。 */
class OperationTypeConst
{
    const CONST_PUT = 'PUT';
    const CONST_UPDATE = 'UPDATE';
    const CONST_DELETE = 'DELETE';

    public static function values()
    {
        return array(
            OperationTypeConst::CONST_PUT,
            OperationTypeConst::CONST_UPDATE,
            OperationTypeConst::CONST_DELETE,
        );
    }

    public static function members()
    {
        return array(
            'OperationTypeConst::CONST_PUT',
            'OperationTypeConst::CONST_UPDATE',
            'OperationTypeConst::CONST_DELETE',
        );
    }
}
