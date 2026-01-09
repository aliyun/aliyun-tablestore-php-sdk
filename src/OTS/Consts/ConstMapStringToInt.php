<?php


namespace Aliyun\OTS\Consts;

use Aliyun\OTS\ProtoBuffer\Protocol\AggregationType;
use Aliyun\OTS\ProtoBuffer\Protocol\DateTimeUnit;
use Aliyun\OTS\ProtoBuffer\Protocol\DecayMathFunction;
use Aliyun\OTS\ProtoBuffer\Protocol\FunctionCombineMode;
use Aliyun\OTS\ProtoBuffer\Protocol\FunctionModifier;
use Aliyun\OTS\ProtoBuffer\Protocol\FunctionScoreMode;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoDistanceType;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoHashPrecision;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByType;
use Aliyun\OTS\ProtoBuffer\Protocol\HighlightEncoder;
use Aliyun\OTS\ProtoBuffer\Protocol\HighlightFragmentOrder;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexUpdateMode;
use Aliyun\OTS\ProtoBuffer\Protocol\MultiValueMode;
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
use Aliyun\OTS\ProtoBuffer\Protocol\VectorDataType;
use Aliyun\OTS\ProtoBuffer\Protocol\VectorMetricType;


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
            case QueryTypeConst::KNN_VECTOR_QUERY:
                return QueryType::KNN_VECTOR_QUERY;
            case QueryTypeConst::FUNCTIONS_SCORE_QUERY:
                return QueryType::FUNCTIONS_SCORE_QUERY;
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
            case FieldTypeConst::VECTOR:
                return FieldType::VECTOR;
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

    public static function FunctionScoreModeMap($key)
    {
        switch ($key) {
            case FunctionScoreModeConst::AVG:
                return FunctionScoreMode::FSM_AVG;
            case FunctionScoreModeConst::MAX:
                return FunctionScoreMode::FSM_MAX;
            case FunctionScoreModeConst::SUM:
                return FunctionScoreMode::FSM_SUM;
            case FunctionScoreModeConst::MIN:
                return FunctionScoreMode::FSM_MIN;
            case FunctionScoreModeConst::FIRST:
                return FunctionScoreMode::FSM_FIRST;
            case FunctionScoreModeConst::MULTIPLY:
                return FunctionScoreMode::FSM_MULTIPLY;
            default:
                throw new \Aliyun\OTS\OTSClientException("function_score_mode should be FunctionScoreModeConst::XXX");
        }
    }


    public static function FunctionCombineModeMap($key)
    {
        switch ($key) {
            case FunctionCombineModeConst::AVG:
                return FunctionCombineMode::FCM_AVG;
            case FunctionCombineModeConst::MAX:
                return FunctionCombineMode::FCM_MAX;
            case FunctionCombineModeConst::SUM:
                return FunctionCombineMode::FCM_SUM;
            case FunctionCombineModeConst::MIN:
                return FunctionCombineMode::FCM_MIN;
            case FunctionCombineModeConst::REPLACE:
                return FunctionCombineMode::FCM_REPLACE;
            case FunctionCombineModeConst::MULTIPLY:
                return FunctionCombineMode::FCM_MULTIPLY;
            default:
                throw new \Aliyun\OTS\OTSClientException("function_combine_mode should be FunctionCombineModeConst::XXX");
        }
    }

    public static function FunctionModifierMap($key)
    {
        switch ($key) {
            case FunctionModifierConst::NONE:
                return FunctionModifier::FM_NONE;
            case FunctionModifierConst::LOG:
                return FunctionModifier::FM_LOG;
            case FunctionModifierConst::LOG1P:
                return FunctionModifier::FM_LOG1P;
            case FunctionModifierConst::LOG2P:
                return FunctionModifier::FM_LOG2P;
            case FunctionModifierConst::LN:
                return FunctionModifier::FM_LN;
            case FunctionModifierConst::LN1P:
                return FunctionModifier::FM_LN1P;
            case FunctionModifierConst::LN2P:
                return FunctionModifier::FM_LN2P;
            case FunctionModifierConst::SQUARE:
                return FunctionModifier::FM_SQUARE;
            case FunctionModifierConst::SQRT:
                return FunctionModifier::FM_SQRT;
            case FunctionModifierConst::RECIPROCAL:
                return FunctionModifier::FM_RECIPROCAL;
            default:
                throw new \Aliyun\OTS\OTSClientException("function_modifier should be FunctionModifierConst::XXX");
        }
    }

    public static function DecayMathFunctionMap($math_function)
    {
        switch ($math_function) {
            case DecayMathFunctionConst::GAUSS:
                return DecayMathFunction::GAUSS;
            case DecayMathFunctionConst::EXP:
                return DecayMathFunction::EXP;
            case DecayMathFunctionConst::LINEAR:
                return DecayMathFunction::LINEAR;
            default:
                throw new \Aliyun\OTS\OTSClientException("math_function should be DecayMathFunctionConst::XXX");
        }
    }

    public static function MultiValueModeMap($multi_value_mode)
    {
        switch ($multi_value_mode) {
            case MultiValueModeConst::MIN:
                return MultiValueMode::MVM_MIN;
            case MultiValueModeConst::MAX:
                return MultiValueMode::MVM_MAX;
            case MultiValueModeConst::AVG:
                return MultiValueMode::MVM_AVG;
            case MultiValueModeConst::SUM:
                return MultiValueMode::MVM_SUM;
            default:
                throw new \Aliyun\OTS\OTSClientException("multi_value_mode should be MultiValueModeConst::XXX");
        }
    }

    public static function GeoHashPrecisionMap($columnValue)
    {
        switch ($columnValue) {
            case GeoHashPrecisionConst::GHP_5009KM_4992KM_1:
                return GeoHashPrecision::GHP_5009KM_4992KM_1;
            case GeoHashPrecisionConst::GHP_1252KM_624KM_2:
                return GeoHashPrecision::GHP_1252KM_624KM_2;
            case GeoHashPrecisionConst::GHP_156KM_156KM_3:
                return GeoHashPrecision::GHP_156KM_156KM_3;
            case GeoHashPrecisionConst::GHP_39KM_19KM_4:
                return GeoHashPrecision::GHP_39KM_19KM_4;
            case GeoHashPrecisionConst::GHP_4900M_4900M_5:
                return GeoHashPrecision::GHP_4900M_4900M_5;
            case GeoHashPrecisionConst::GHP_1200M_609M_6:
                return GeoHashPrecision::GHP_1200M_609M_6;
            case GeoHashPrecisionConst::GHP_152M_152M_7:
                return GeoHashPrecision::GHP_152M_152M_7;
            case GeoHashPrecisionConst::GHP_38M_19M_8:
                return GeoHashPrecision::GHP_38M_19M_8;
            case GeoHashPrecisionConst::GHP_480CM_480CM_9:
                return GeoHashPrecision::GHP_480CM_480CM_9;
            case GeoHashPrecisionConst::GHP_120CM_595MM_10:
                return GeoHashPrecision::GHP_120CM_595MM_10;
            case GeoHashPrecisionConst::GHP_149MM_149MM_11:
                return GeoHashPrecision::GHP_149MM_149MM_11;
            case GeoHashPrecisionConst::GHP_37MM_19MM_12:
                return GeoHashPrecision::GHP_37MM_19MM_12;
            default:
                throw new \Aliyun\OTS\OTSClientException("GeoHashPrecision must be one of GeoHashPrecisionConst::XXX");
        }
    }

    public static function DateTimeUnitMap($unit)
    {
        switch ($unit) {
            case DateTimeUnitConst::YEAR:
                return DateTimeUnit::YEAR;
            case DateTimeUnitConst::QUARTER_YEAR:
                return DateTimeUnit::QUARTER_YEAR;
            case DateTimeUnitConst::MONTH:
                return DateTimeUnit::MONTH;
            case DateTimeUnitConst::WEEK:
                return DateTimeUnit::WEEK;
            case DateTimeUnitConst::DAY:
                return DateTimeUnit::DAY;
            case DateTimeUnitConst::HOUR:
                return DateTimeUnit::HOUR;
            case DateTimeUnitConst::MINUTE:
                return DateTimeUnit::MINUTE;
            case DateTimeUnitConst::SECOND:
                return DateTimeUnit::SECOND;
            case DateTimeUnitConst::MILLISECOND:
                return DateTimeUnit::MILLISECOND;
            default:
                throw new \Aliyun\OTS\OTSClientException("DateTimeUnit must be one of DateTimeUnitConst::XXX, ");
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
            case GroupByTypeConst::GROUP_BY_DATE_HISTOGRAM:
                return GroupByType::GROUP_BY_DATE_HISTOGRAM;
            case GroupByTypeConst::GROUP_BY_GEO_GRID:
                return GroupByType::GROUP_BY_GEO_GRID;
            case GroupByTypeConst::GROUP_BY_COMPOSITE:
                return GroupByType::GROUP_BY_COMPOSITE;
            default:
                return null;
        }
    }

    public static function IndexTypeMap($key)
    {
        switch ($key) {
            case IndexTypeConst::GLOBAL_INDEX:
                return IndexType::IT_GLOBAL_INDEX;
            case IndexTypeConst::LOCAL_INDEX:
                return IndexType::IT_LOCAL_INDEX;
            default:
                return null;
        }
    }

    public static function IndexUpdateModeMap($key)
    {
        switch ($key) {
            case IndexUpdateModeConst::ASYNC_INDEX:
                return IndexUpdateMode::IUM_ASYNC_INDEX;
            case IndexUpdateModeConst::SYNC_INDEX:
                return IndexUpdateMode::IUM_SYNC_INDEX;
            default:
                return null;
        }
    }

    public static function VectorDataTypeMap($data_type)
    {
        switch ($data_type) {
            case VectorDataTypeConst::FLOAT_32:
                return VectorDataType::VD_FLOAT_32;
            default:
                throw new \Aliyun\OTS\OTSClientException("vector_data_type should be VectorDataTypeConst::XXX");
        }
    }

    public static function VectorMetricTypeMap($metric_type)
    {
        switch ($metric_type) {
            case VectorMetricTypeConst::DOT_PRODUCT:
                return VectorMetricType::VM_DOT_PRODUCT;
            case VectorMetricTypeConst::COSINE:
                return VectorMetricType::VM_COSINE;
            case VectorMetricTypeConst::EUCLIDEAN:
                return VectorMetricType::VM_EUCLIDEAN;
            default:
                throw new \Aliyun\OTS\OTSClientException("vector_metric_type should be VectorMetricTypeConst::XXX");
        }
    }

    public static function HighlightEncoderMap($highlight_encoder)
    {
        switch ($highlight_encoder) {
            case HighlightEncoderConst::PLAIN:
                return HighlightEncoder::PLAIN_MODE;
            case HighlightEncoderConst::HTML:
                return HighlightEncoder::HTML_MODE;
            default:
                throw new \Aliyun\OTS\OTSClientException("highlight_encoder should be HighlightEncoderConst::XXX");
        }
    }

    public static function HighlightFragmentOrderMap($highlight_fragment_order)
    {
        switch ($highlight_fragment_order) {
            case HighlightFragmentOrderConst::SCORE:
                return HighlightFragmentOrder::SCORE;
            case HighlightFragmentOrderConst::TEXT_SEQUENCE:
                return HighlightFragmentOrder::TEXT_SEQUENCE;
            default:
                throw new \Aliyun\OTS\OTSClientException("highlight_fragment_order should be HighlightFragmentOrderConst::XXX");
        }
    }
}