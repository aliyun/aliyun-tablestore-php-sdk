<?php


namespace Aliyun\OTS\Consts;

use Aliyun\OTS\ProtoBuffer\Protocol\AggregationType;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoDistanceType;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexUpdateMode;
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


class DateTimeUnitConst
{
    const YEAR = "YEAR";
    const QUARTER_YEAR = "QUARTER_YEAR";
    const MONTH = "MONTH";
    const WEEK = "WEEK";
    const DAY = "DAY";
    const HOUR = "HOUR";
    const MINUTE = "MINUTE";
    const SECOND = "SECOND";
    const MILLISECOND = "MILLISECOND";
}