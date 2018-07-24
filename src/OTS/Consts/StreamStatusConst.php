<?php

namespace Aliyun\OTS\Consts;

/* 该类被使用于描述Stream状态的数据类型。 */
class StreamStatusConst
{
    const CONST_ENABLING = 'ENABLING';
    const CONST_ACTIVE = 'ACTIVE';

    public static function values()
    {
        return array(
            StreamStatusConst::CONST_ENABLING,
            StreamStatusConst::CONST_ACTIVE
        );
    }

    public static function members()
    {
        return array(
            'StreamStatusConst::CONST_ENABLING',
            'StreamStatusConst::CONST_ACTIVE'
        );
    }
}
