<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述属性列改变类型RowChange的数据类型。 */
class UpdateTypeConst
{
    const CONST_PUT = 'PUT';
    const CONST_DELETE = 'DELETE';
    const CONST_DELETE_ALL = 'DELETE_ALL';
    const CONST_INCREMENT = 'INCREMENT';

    public static function values()
    {
        return array(
            UpdateTypeConst::CONST_PUT,
            UpdateTypeConst::CONST_DELETE,
            UpdateTypeConst::CONST_DELETE_ALL,
            UpdateTypeConst::CONST_INCREMENT
        );
    }

    public static function members()
    {
        return array(
            'UpdateTypeConst::CONST_PUT',
            'UpdateTypeConst::CONST_DELETE',
            'UpdateTypeConst::CONST_DELETE_ALL'
        );
    }
}
