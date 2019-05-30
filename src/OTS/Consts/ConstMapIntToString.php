<?php


namespace Aliyun\OTS\Consts;

use Aliyun\OTS\ProtoBuffer\Protocol\DefinedColumnType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\QueryType;
use Aliyun\OTS\ProtoBuffer\Protocol\SortMode;
use Aliyun\OTS\ProtoBuffer\Protocol\SortOrder;
use Aliyun\OTS\ProtoBuffer\Protocol\FieldType;
use Aliyun\OTS\ProtoBuffer\Protocol\SyncPhase;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoDistanceType;


class ConstMapIntToString
{
    public static function QueryTypeMap($key)
    {
        switch ($key) {
            case QueryType::MATCH_QUERY:
                return QueryTypeConst::MATCH_QUERY;
            case QueryType::MATCH_PHRASE_QUERY:
                return QueryTypeConst::MATCH_PHRASE_QUERY;
            case QueryType::TERM_QUERY:
                return QueryTypeConst::TERM_QUERY;
            case QueryType::RANGE_QUERY:
                return QueryTypeConst::RANGE_QUERY;
            case QueryType::PREFIX_QUERY:
                return QueryTypeConst::PREFIX_QUERY;
            case QueryType::BOOL_QUERY:
                return QueryTypeConst::BOOL_QUERY;
            case QueryType::CONST_SCORE_QUERY:
                return QueryTypeConst::CONST_SCORE_QUERY;
            case QueryType::FUNCTION_SCORE_QUERY:
                return QueryTypeConst::FUNCTION_SCORE_QUERY;
            case QueryType::NESTED_QUERY:
                return QueryTypeConst::NESTED_QUERY;
            case QueryType::WILDCARD_QUERY:
                return QueryTypeConst::WILDCARD_QUERY;
            case QueryType::MATCH_ALL_QUERY:
                return QueryTypeConst::MATCH_ALL_QUERY;
            case QueryType::GEO_BOUNDING_BOX_QUERY:
                return QueryTypeConst::GEO_BOUNDING_BOX_QUERY;
            case QueryType::GEO_DISTANCE_QUERY:
                return QueryTypeConst::GEO_DISTANCE_QUERY;
            case QueryType::GEO_POLYGON_QUERY:
                return QueryTypeConst::GEO_POLYGON_QUERY;
            case QueryType::TERMS_QUERY:
                return QueryTypeConst::TERMS_QUERY;
            case QueryType::EXISTS_QUERY:
                return QueryTypeConst::EXISTS_QUERY;
            default:
                return null;
        }
    }

    public static function SortOrderMap($key)
    {
        switch ($key) {
            case SortOrder::SORT_ORDER_ASC:
                return SortOrderConst::SORT_ORDER_ASC;
            case SortOrder::SORT_ORDER_DESC:
                return SortOrderConst::SORT_ORDER_DESC;
            default:
                return null;
        }
    }

    public static function SortModeMap($key)
    {
        switch ($key) {
            case SortMode::SORT_MODE_MIN:
                return SortModeConst::SORT_MODE_MIN;
            case SortMode::SORT_MODE_MAX:
                return SortModeConst::SORT_MODE_MAX;
            case SortMode::SORT_MODE_AVG:
                return SortModeConst::SORT_MODE_AVG;
            default:
                return null;
        }
    }

    public static function FieldTypeMap($key)
    {
        switch ($key) {
            case FieldType::LONG:
                return FieldTypeConst::LONG;
            case FieldType::DOUBLE:
                return FieldTypeConst::DOUBLE;
            case FieldType::BOOLEAN:
                return FieldTypeConst::BOOLEAN;
            case FieldType::KEYWORD:
                return FieldTypeConst::KEYWORD;
            case FieldType::TEXT:
                return FieldTypeConst::TEXT;
            case FieldType::NESTED:
                return FieldTypeConst::NESTED;
            case FieldType::GEO_POINT:
                return FieldTypeConst::GEO_POINT;
            default:
                return null;
        }
    }

    public static function IndexOptionsMap($key)
    {
        switch ($key) {
            case IndexOptions::DOCS:
                return IndexOptionsConst::DOCS;
            case IndexOptions::FREQS:
                return IndexOptionsConst::FREQS;
            case IndexOptions::POSITIONS:
                return IndexOptionsConst::POSITIONS;
            case IndexOptions::OFFSETS:
                return IndexOptionsConst::OFFSETS;
            default:
                return null;
        }
    }

    public static function SyncPhaseMap($key)
    {
        switch ($key) {
            case SyncPhase::FULL:
                return SyncPhaseConst::FULL;
            case SyncPhase::INCR:
                return SyncPhaseConst::INCR;
            default:
                return null;
        }
    }

    public static function GeoDistanceTypeMap($key)
    {
        switch ($key) {
            case GeoDistanceType::GEO_DISTANCE_ARC:
                return GeoDistanceTypeConst::GEO_DISTANCE_ARC;
            case GeoDistanceType::GEO_DISTANCE_PLANE:
                return GeoDistanceTypeConst::GEO_DISTANCE_PLANE;
            default:
                return null;
        }
    }

    public static function DefinedColumnTypeMap($key)
    {
        switch ($key) {
            case DefinedColumnType::DCT_INTEGER:
                return DefinedColumnTypeConst::DCT_INTEGER;
            case DefinedColumnType::DCT_DOUBLE:
                return DefinedColumnTypeConst::DCT_DOUBLE;
            case DefinedColumnType::DCT_BOOLEAN:
                return DefinedColumnTypeConst::DCT_BOOLEAN;
            case DefinedColumnType::DCT_STRING:
                return DefinedColumnTypeConst::DCT_STRING;
            case DefinedColumnType::DCT_BLOB:
                return DefinedColumnTypeConst::DCT_BLOB;
            default:
                return null;
        }
    }}