<?php


namespace Aliyun\OTS\Consts;

use Aliyun\OTS\ProtoBuffer\Protocol\AggregationType;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoDistanceType;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\QueryType;
use Aliyun\OTS\ProtoBuffer\Protocol\SortMode;
use Aliyun\OTS\ProtoBuffer\Protocol\SortOrder;
use Aliyun\OTS\ProtoBuffer\Protocol\FieldType;
use Aliyun\OTS\ProtoBuffer\Protocol\SQLPayloadVersion;
use Aliyun\OTS\ProtoBuffer\Protocol\SQLStatementType;
use Aliyun\OTS\ProtoBuffer\Protocol\SyncPhase;
use Aliyun\OTS\ProtoBuffer\Protocol\ColumnReturnType;
use Aliyun\OTS\ProtoBuffer\Protocol\QueryOperator;
use Aliyun\OTS\ProtoBuffer\Protocol\ScoreMode;
use Aliyun\OTS\ProtoBuffer\Protocol\DefinedColumnType;


class ConstMapStringToInt
{
    public static function QueryTypeMap($key)
    {
        switch ($key) {
            case QueryTypeConst::MATCH_QUERY:
                return QueryType::MATCH_QUERY;
            case QueryTypeConst::MATCH_PHRASE_QUERY:
                return QueryType::MATCH_PHRASE_QUERY;
            case QueryTypeConst::TERM_QUERY:
                return QueryType::TERM_QUERY;
            case QueryTypeConst::RANGE_QUERY:
                return QueryType::RANGE_QUERY;
            case QueryTypeConst::PREFIX_QUERY:
                return QueryType::PREFIX_QUERY;
            case QueryTypeConst::BOOL_QUERY:
                return QueryType::BOOL_QUERY;
            case QueryTypeConst::CONST_SCORE_QUERY:
                return QueryType::CONST_SCORE_QUERY;
            case QueryTypeConst::FUNCTION_SCORE_QUERY:
                return QueryType::FUNCTION_SCORE_QUERY;
            case QueryTypeConst::NESTED_QUERY:
                return QueryType::NESTED_QUERY;
            case QueryTypeConst::WILDCARD_QUERY:
                return QueryType::WILDCARD_QUERY;
            case QueryTypeConst::MATCH_ALL_QUERY:
                return QueryType::MATCH_ALL_QUERY;
            case QueryTypeConst::GEO_BOUNDING_BOX_QUERY:
                return QueryType::GEO_BOUNDING_BOX_QUERY;
            case QueryTypeConst::GEO_DISTANCE_QUERY:
                return QueryType::GEO_DISTANCE_QUERY;
            case QueryTypeConst::GEO_POLYGON_QUERY:
                return QueryType::GEO_POLYGON_QUERY;
            case QueryTypeConst::TERMS_QUERY:
                return QueryType::TERMS_QUERY;
            case QueryTypeConst::EXISTS_QUERY:
                return QueryType::EXISTS_QUERY;
            default:
                throw new \Aliyun\OTS\OTSClientException("query_type should be QueryTypeConst::XXX");
        }
    }

    public static function SortOrderMap($key)
    {
        switch ($key) {
            case SortOrderConst::SORT_ORDER_ASC:
                return SortOrder::SORT_ORDER_ASC;
            case SortOrderConst::SORT_ORDER_DESC:
                return SortOrder::SORT_ORDER_DESC;
            default:
                throw new \Aliyun\OTS\OTSClientException("order should be SortOrderConst::XXX");
        }
    }

    public static function SortModeMap($key)
    {
        switch ($key) {
            case SortModeConst::SORT_MODE_MIN:
                return SortMode::SORT_MODE_MIN;
            case SortModeConst::SORT_MODE_MAX:
                return SortMode::SORT_MODE_MAX;
            case SortModeConst::SORT_MODE_AVG:
                return SortMode::SORT_MODE_AVG;
            default:
                throw new \Aliyun\OTS\OTSClientException("mode should be SortModeConst::XXX");
        }
    }

    public static function GeoDistanceTypeMap($key)
    {
        switch ($key) {
            case GeoDistanceTypeConst::GEO_DISTANCE_ARC:
                return GeoDistanceTypeConst::GEO_DISTANCE_ARC;
            case GeoDistanceTypeConst::GEO_DISTANCE_PLANE:
                return GeoDistanceType::GEO_DISTANCE_PLANE;
            default:
                throw new \Aliyun\OTS\OTSClientException("distance_type should be GeoDistanceTypeConst::XXX");
        }
    }

    public static function FieldTypeMap($key)
    {
        switch ($key) {
            case FieldTypeConst::LONG:
                return FieldType::LONG;
            case FieldTypeConst::DOUBLE:
                return FieldType::DOUBLE;
            case FieldTypeConst::BOOLEAN:
                return FieldType::BOOLEAN;
            case FieldTypeConst::KEYWORD:
                return FieldType::KEYWORD;
            case FieldTypeConst::TEXT:
                return FieldType::TEXT;
            case FieldTypeConst::NESTED:
                return FieldType::NESTED;
            case FieldTypeConst::GEO_POINT:
                return FieldType::GEO_POINT;
            case FieldTypeConst::DATE:
                return FieldType::DATE;
            default:
                throw new \Aliyun\OTS\OTSClientException("field_type should be FieldTypeConst::XXX");
        }
    }

    public static function IndexOptionsMap($key)
    {
        switch ($key) {
            case IndexOptionsConst::DOCS:
                return IndexOptions::DOCS;
            case IndexOptionsConst::FREQS:
                return IndexOptions::FREQS;
            case IndexOptionsConst::POSITIONS:
                return IndexOptions::POSITIONS;
            case IndexOptionsConst::OFFSETS:
                return IndexOptions::OFFSETS;
            default:
                throw new \Aliyun\OTS\OTSClientException("index_options should be IndexOptionsConst::XXX");
        }
    }

    public static function SyncPhaseMap($key)
    {
        switch ($key) {
            case SyncPhaseConst::FULL:
                return SyncPhase::FULL;
            case SyncPhaseConst::INCR:
                return SyncPhase::INCR;
            default:
                throw new \Aliyun\OTS\OTSClientException("sync_phase should be SyncPhaseConst::XXX");
        }
    }

    public static function ColumnReturnTypeMap($key)
    {
        switch ($key) {
            case ColumnReturnTypeConst::RETURN_ALL:
                return ColumnReturnType::RETURN_ALL;
            case ColumnReturnTypeConst::RETURN_SPECIFIED:
                return ColumnReturnType::RETURN_SPECIFIED;
            case ColumnReturnTypeConst::RETURN_NONE:
                return ColumnReturnType::RETURN_NONE;
            case ColumnReturnTypeConst::RETURN_ALL_FROM_INDEX:
                return ColumnReturnType::RETURN_ALL_FROM_INDEX;
            default:
                throw new \Aliyun\OTS\OTSClientException("return_type should be ColumnReturnTypeConst::XXX");
        }
    }

    public static function QueryOperatorMap($key)
    {
        switch ($key) {
            case QueryOperatorConst::PBAND:
                return QueryOperator::PBAND;
            case QueryOperatorConst::PBOR:
                return QueryOperator::PBOR;
            default:
                throw new \Aliyun\OTS\OTSClientException("operator should be QueryOperatorConst::XXX");
        }
    }

    public static function ScoreModeMap($key)
    {
        switch ($key) {
            case ScoreModeConst::SCORE_MODE_NONE:
                return ScoreMode::SCORE_MODE_NONE;
            case ScoreModeConst::SCORE_MODE_AVG:
                return ScoreMode::SCORE_MODE_AVG;
            case ScoreModeConst::SCORE_MODE_MAX:
                return ScoreMode::SCORE_MODE_MAX;
            case ScoreModeConst::SCORE_MODE_TOTAL:
                return ScoreMode::SCORE_MODE_TOTAL;
            case ScoreModeConst::SCORE_MODE_MIN:
                return ScoreMode::SCORE_MODE_MIN;
            default:
                throw new \Aliyun\OTS\OTSClientException("score_mode should be ScoreModeConst::XXX");
        }
    }

    public static function DefinedColumnTypeMap($key)
    {
        switch ($key) {
            case DefinedColumnTypeConst::DCT_INTEGER:
                return DefinedColumnType::DCT_INTEGER;
            case DefinedColumnTypeConst::DCT_DOUBLE:
                return DefinedColumnType::DCT_DOUBLE;
            case DefinedColumnTypeConst::DCT_BOOLEAN:
                return DefinedColumnType::DCT_BOOLEAN;
            case DefinedColumnTypeConst::DCT_STRING:
                return DefinedColumnType::DCT_STRING;
            case DefinedColumnTypeConst::DCT_BLOB:
                return DefinedColumnType::DCT_BLOB;
            default:
                throw new \Aliyun\OTS\OTSClientException("defined_column_type should be DefinedColumnTypeConst::XXX");
        }
    }

    public static function SQLStatementTypeMap($key)
    {
        switch ($key) {
            case SQLStatementTypeConst::DCT_SQL_SELECT:
                return SQLStatementType::SQL_SELECT;
            case SQLStatementTypeConst::DCT_SQL_CREATE_TABLE:
                return SQLStatementType::SQL_CREATE_TABLE;
            case SQLStatementTypeConst::DCT_SQL_SHOW_TABLE:
                return SQLStatementType::SQL_SHOW_TABLE;
            case SQLStatementTypeConst::DCT_SQL_DESCRIBE_TABLE:
                return SQLStatementType::SQL_DESCRIBE_TABLE;
            case SQLStatementTypeConst::DCT_SQL_DROP_TABLE:
                return SQLStatementType::SQL_DROP_TABLE;
            case SQLStatementTypeConst::DCT_SQL_ALTER_TABLE:
                return SQLStatementType::SQL_ALTER_TABLE;
            default:
                return null;
        }
    }

    public static function SQLPayloadVersionMap($key)
    {
        switch ($key) {
            case SQLPayloadVersionConst::SQL_PLAIN_BUFFER:
                return SQLPayloadVersion::SQL_PLAIN_BUFFER;
            case SQLPayloadVersionConst::SQL_FLAT_BUFFERS:
                return SQLPayloadVersion::SQL_FLAT_BUFFERS;
            default:
                return null;
        }
    }

    public static function AggregationTypeMap($key)
    {
        switch ($key) {
            case AggregationTypeConst::AGG_AVG:
                return AggregationType::AGG_AVG;
            case AggregationTypeConst::AGG_MAX:
                return AggregationType::AGG_MAX;
            case AggregationTypeConst::AGG_MIN:
                return AggregationType::AGG_MIN;
            case AggregationTypeConst::AGG_SUM:
                return AggregationType::AGG_SUM;
            case AggregationTypeConst::AGG_COUNT:
                return AggregationType::AGG_COUNT;
            case AggregationTypeConst::AGG_DISTINCT_COUNT:
                return AggregationType::AGG_DISTINCT_COUNT;
            case AggregationTypeConst::AGG_TOP_ROWS:
                return AggregationType::AGG_TOP_ROWS;
            case AggregationTypeConst::AGG_PERCENTILES:
                return AggregationType::AGG_PERCENTILES;
            default:
                return null;
        }
    }

    public static function GroupByTypeMap($key)
    {
        switch ($key) {
            case GroupByTypeConst::GROUP_BY_FIELD:
                return GroupByType::GROUP_BY_FIELD;
            case GroupByTypeConst::GROUP_BY_RANGE:
                return GroupByType::GROUP_BY_RANGE;
            case GroupByTypeConst::GROUP_BY_FILTER:
                return GroupByType::GROUP_BY_FILTER;
            case GroupByTypeConst::GROUP_BY_GEO_DISTANCE:
                return GroupByType::GROUP_BY_GEO_DISTANCE;
            case GroupByTypeConst::GROUP_BY_HISTOGRAM:
                return GroupByType::GROUP_BY_HISTOGRAM;
            default:
                return null;
        }
    }
}