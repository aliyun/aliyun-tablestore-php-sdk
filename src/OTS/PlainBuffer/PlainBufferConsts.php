<?php

namespace Aliyun\OTS\PlainBuffer;


class PlainBufferConsts
{
    const HEADER = 0x75;

    // tag type
    const TAG_ROW_PK = 0x1;
    const TAG_ROW_DATA = 0x2;
    const TAG_CELL = 0x3;
    const TAG_CELL_NAME = 0x4;
    const TAG_CELL_VALUE = 0x5;
    const TAG_CELL_TYPE = 0x6;
    const TAG_CELL_TIMESTAMP = 0x7;
    const TAG_DELETE_ROW_MARKER = 0x8;
    const TAG_ROW_CHECKSUM = 0x9;
    const TAG_CELL_CHECKSUM = 0x0A;

    // cell op type
    const DELETE_ALL_VERSION = 0x1;
    const DELETE_ONE_VERSION = 0x3;

    // variant type
    const VT_INTEGER = 0x0;
    const VT_DOUBLE = 0x1;
    const VT_BOOLEAN = 0x2;
    const VT_STRING = 0x3;

    const VT_NULL = 0x6;
    const VT_BLOB = 0x7;
    const VT_INF_MIN = 0x9;
    const VT_INF_MAX = 0xa;
    const VT_AUTO_INCREMENT = 0xb;

    const LITTLE_ENDIAN_32_SIZE = 4;
    const LITTLE_ENDIAN_64_SIZE = 8;

    public function printTag($tag)
    {
        switch ($tag) {
            case self::TAG_ROW_PK:
                return "TAG_ROW_PK";
            case self::TAG_ROW_DATA:
                return "TAG_ROW_DATA";
            case self::TAG_CELL:
                return "TAG_CELL";
            case self::TAG_CELL_NAME:
                return "TAG_CELL_NAME";
            case self::TAG_CELL_VALUE:
                return "TAG_CELL_VALUE";
            case self::TAG_CELL_TYPE:
                return "TAG_CELL_TYPE";
            case self::TAG_CELL_TIMESTAMP:
                return "TAG_CELL_TIMESTAMP";
            case self::TAG_DELETE_ROW_MARKER:
                return "TAG_DELETE_ROW_MARKER";
            case self::TAG_ROW_CHECKSUM:
                return "TAG_ROW_CHECKSUM";
            case self::TAG_CELL_CHECKSUM:
                return "TAG_CELL_CHECKSUM";
            default:
                return "UNKNOWN_TAG(" . $tag . ")";
        }
    }

    public function isUnknownTag($tag)
    {
        switch ($tag) {
            case self::TAG_ROW_PK:
            case self::TAG_ROW_DATA:
            case self::TAG_CELL:
            case self::TAG_CELL_NAME:
            case self::TAG_CELL_VALUE:
            case self::TAG_CELL_TYPE:
            case self::TAG_CELL_TIMESTAMP:
            case self::TAG_DELETE_ROW_MARKER:
            case self::TAG_ROW_CHECKSUM:
            case self::TAG_CELL_CHECKSUM:
                return false;
            default:
                return true;
        }
    }
}


