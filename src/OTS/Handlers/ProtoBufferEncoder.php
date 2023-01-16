<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;
use Aliyun\OTS\Consts\AggregationTypeConst;
use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\GroupByTypeConst;
use Aliyun\OTS\Consts\LogicalOperatorConst;
use Aliyun\OTS\Consts\OperationTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyOptionConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\Consts\UpdateTypeConst;
use Aliyun\OTS\PlainBuffer\PlainBufferBuilder;
use Aliyun\OTS\ProtoBuffer\Protocol\BatchGetRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\BatchWriteRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\CapacityUnit;
use Aliyun\OTS\ProtoBuffer\Protocol\ColumnPaginationFilter;
use Aliyun\OTS\ProtoBuffer\Protocol\CompositeColumnValueFilter;
use Aliyun\OTS\ProtoBuffer\Protocol\ComputeSplitPointsBySizeRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\Condition;
use Aliyun\OTS\ProtoBuffer\Protocol\CreateTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DeleteRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DeleteTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DescribeStreamRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DescribeTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\Direction;
use Aliyun\OTS\ProtoBuffer\Protocol\Filter;
use Aliyun\OTS\ProtoBuffer\Protocol\FilterType;
use Aliyun\OTS\ProtoBuffer\Protocol\GetRangeRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\GetRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\GetShardIteratorRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\GetStreamRecordRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\ListStreamRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\OperationType;
use Aliyun\OTS\ProtoBuffer\Protocol\PrimaryKeySchema;
use Aliyun\OTS\ProtoBuffer\Protocol\PrimaryKeyType;
use Aliyun\OTS\ProtoBuffer\Protocol\PutRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\ReservedThroughput;
use Aliyun\OTS\ProtoBuffer\Protocol\ReturnContent;
use Aliyun\OTS\ProtoBuffer\Protocol\ReturnType;
use Aliyun\OTS\ProtoBuffer\Protocol\RowExistenceExpectation;
use Aliyun\OTS\ProtoBuffer\Protocol\RowInBatchWriteRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\SingleColumnValueFilter;
use Aliyun\OTS\ProtoBuffer\Protocol\StreamSpecification;
use Aliyun\OTS\ProtoBuffer\Protocol\TableInBatchGetRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\TableInBatchWriteRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\TableMeta;
use Aliyun\OTS\ProtoBuffer\Protocol\TableOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\TimeRange;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\ListSearchIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DescribeSearchIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\CreateSearchIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\FieldSchema;
use Aliyun\OTS\ProtoBuffer\Protocol\FieldType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexSchema;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexSetting;
use Aliyun\OTS\ProtoBuffer\Protocol\Sort;
use Aliyun\OTS\ProtoBuffer\Protocol\Sorter;
use Aliyun\OTS\ProtoBuffer\Protocol\FieldSort;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoDistanceSort;
use Aliyun\OTS\ProtoBuffer\Protocol\ScoreSort;
use Aliyun\OTS\ProtoBuffer\Protocol\PrimaryKeySort;
use Aliyun\OTS\ProtoBuffer\Protocol\ColumnType;
use Aliyun\OTS\ProtoBuffer\Protocol\QueryType;
use Aliyun\OTS\ProtoBuffer\Protocol\DeleteSearchIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateSearchIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\ComputeSplitsRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\SearchIndexSplitsOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\ParallelScanRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\SearchRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\ColumnsToGet;
use Aliyun\OTS\ProtoBuffer\Protocol\SearchQuery;
use Aliyun\OTS\ProtoBuffer\Protocol\Collapse;
use Aliyun\OTS\ProtoBuffer\Protocol\Query;
use Aliyun\OTS\ProtoBuffer\Protocol\ScanQuery;

use Aliyun\OTS\ProtoBuffer\Protocol\Aggregations;
use Aliyun\OTS\ProtoBuffer\Protocol\Aggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\AvgAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\MaxAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\MinAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\SumAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\CountAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\DistinctCountAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\TopRowsAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\PercentilesAggregation;
use Aliyun\OTS\ProtoBuffer\Protocol\AggregationType;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByType;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupBys;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupBy;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByRange;
use Aliyun\OTS\ProtoBuffer\Protocol\Range;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByFilter;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByGeoDistance;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByHistogram;
use Aliyun\OTS\ProtoBuffer\Protocol\GeoPoint;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupByField;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupBySort;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupBySorter;
use Aliyun\OTS\ProtoBuffer\Protocol\RowCountSort;
use Aliyun\OTS\ProtoBuffer\Protocol\GroupKeySort;
use Aliyun\OTS\ProtoBuffer\Protocol\SubAggSort;
use Aliyun\OTS\ProtoBuffer\Protocol\FieldRange;

use Aliyun\OTS\ProtoBuffer\Protocol\SingleWordAnalyzerParameter;
use Aliyun\OTS\ProtoBuffer\Protocol\SplitAnalyzerParameter;
use Aliyun\OTS\ProtoBuffer\Protocol\FuzzyAnalyzerParameter;
use Aliyun\OTS\ProtoBuffer\Protocol\DefinedColumnSchema;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexMeta;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexType;
use Aliyun\OTS\ProtoBuffer\Protocol\IndexUpdateMode;
use Aliyun\OTS\ProtoBuffer\Protocol\CreateIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DropIndexRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\StartLocalTransactionRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\CommitTransactionRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\AbortTransactionRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\SQLQueryRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\SQLPayloadVersion;
use Aliyun\OTS\ProtoBuffer\Protocol\SQLStatementType;




use Aliyun\OTS\Consts\ConstMapStringToInt;


class ProtoBufferEncoder
{
    private function checkParameter($request)
    {
        // TODO implement
    }

    private function preprocessPrimaryKeyValueType($type)
    {
        switch ($type) {
            case 'INTEGER': return PrimaryKeyTypeConst::CONST_INTEGER;
            case 'STRING': return PrimaryKeyTypeConst::CONST_STRING;
            case 'BINARY': return PrimaryKeyTypeConst::CONST_BINARY;
            case 'INF_MIN': return PrimaryKeyTypeConst::CONST_INF_MIN;
            case 'INF_MAX': return PrimaryKeyTypeConst::CONST_INF_MAX;
            case 'PK_AUTO_INCR': return PrimaryKeyTypeConst::CONST_PK_AUTO_INCR;
            default:
                throw new \Aliyun\OTS\OTSClientException("PrimaryKey type must be one of 'INTEGER', 'STRING', 'BINARY' ");
        }
    }

    private function preprocessPrimaryKeyType($type)
    {
        switch ($type) {
            case 'INTEGER': {
                return PrimaryKeyType::INTEGER;
            }
            case 'STRING': {
                return PrimaryKeyType::STRING;
            }
            case 'BINARY': {
                return PrimaryKeyType::BINARY;
            }
            default:
                throw new \Aliyun\OTS\OTSClientException("PrimaryKey type must be one of 'INTEGER', 'STRING', 'BINARY' ");
        }
    }

    private function preprocessPrimaryKeyOption($option)
    {
        switch ($option) {
            case PrimaryKeyOptionConst::CONST_PK_AUTO_INCR:
                {
                    return OTS\ProtoBuffer\Protocol\PrimaryKeyOption::AUTO_INCREMENT;
                }
            default:
                throw new \Aliyun\OTS\OTSClientException("PrimaryKey option must be one of 'PK_AUTO_INCR");
        }

    }

    private function preprocessColumnType($type)
    {
        switch ($type) {
            case 'INTEGER': return ColumnTypeConst::CONST_INTEGER;
            case 'BOOLEAN': return ColumnTypeConst::CONST_BOOLEAN;
            case 'DOUBLE': return ColumnTypeConst::CONST_DOUBLE;
            case 'STRING': return ColumnTypeConst::CONST_STRING;
            case 'BINARY': return ColumnTypeConst::CONST_BINARY;
            default:
                throw new \Aliyun\OTS\OTSClientException("Column type must be one of 'INTEGER', 'STRING', 'BINARY', 'DOUBLE', 'BOOLEAN'");
        }
    }

    private function preprocessReturnType($type)
    {
        switch ($type) {
            case ReturnTypeConst::CONST_PK: return ReturnType::RT_PK;
            case ReturnTypeConst::CONST_NONE: return ReturnType::RT_NONE;
            case ReturnTypeConst::CONST_AFTER_MODIFY: return ReturnType::RT_AFTER_MODIFY;
            default:
                throw new \Aliyun\OTS\OTSClientException("return type must be one of 'ReturnTypeConst::CONST_PK', 'ReturnTypeConst::CONST_NONE");
        }
    }

    private function preprocessColumnValue($columnValue)
    {
        $value = $columnValue;
        $ret = array();

        $type = ColumnTypeConst::CONST_STRING;
        if(is_array($columnValue) && isset($columnValue['value'])) {
            $value = $columnValue['value'];
        }
        if(is_array($columnValue) && isset($columnValue['type'])) {
            $type = $columnValue['type'];
        }
        if (is_bool($value)) {

            // is_bool() is checked before is_int(), to avoid type upcasting
            $ret['type'] = ColumnTypeConst::CONST_BOOLEAN;
        } else if (is_int($value)) {
            $ret['type'] = ColumnTypeConst::CONST_INTEGER;
        } else if (is_string($value)) {
            $ret['type'] = $type;
        } else if (is_double($value) || is_float($value)) {
            $ret['type'] = ColumnTypeConst::CONST_DOUBLE;
        } else if (is_array($columnValue)) {
            if (!isset($columnValue['type']) && !isset($columnValue['timestamp'])) {
                throw new \Aliyun\OTS\OTSClientException("An array column value must has 'type'or 'timestamp' field.");
            }
        } else {
            throw new \Aliyun\OTS\OTSClientException("A column value must be a int, string, bool, double, float, or array.");
        }

        if(!empty($columnValue['type'])) {
            $type = $this->preprocessColumnType($columnValue['type']);
            $ret['type'] = $type;
        }
        $ret['value'] = $value;

        if(!empty($columnValue['timestamp'])) {
            $ret['timestamp'] = $columnValue['timestamp'];
        } else {
            $ret['timestamp'] = null;
        }
        return $ret;
    }

    private function preprocessPrimaryKeyValue($pkValue, $pkType)
    {
        if(!isset($pkType)) {
            if (is_int($pkValue)) {
                $pkType =  PrimaryKeyTypeConst::CONST_INTEGER;
            } else if (is_string($pkValue)) {
                $pkType =  PrimaryKeyTypeConst::CONST_STRING;
            }
        }

        if (is_null($pkValue)) {
            if ($pkType != 'INF_MIN' && $pkType != 'INF_MAX' && $pkType != 'PK_AUTO_INCR') {
                throw new \Aliyun\OTS\OTSClientException("A primarykey value wth type INTEGER, STRING, or BINARY must has 'value' field.");
            }
        }

        if(isset($pkType)) {
            $type = $this->preprocessPrimaryKeyValueType($pkType);
        }
        $ret = array('type' => $type);

        if(isset($pkValue)) {
            $ret['value'] = $pkValue;
        } else {
            $ret['value'] = null;
        }

        return $ret;
    }

    private function preprocessPrimaryKey($primayKey)
    {
        $ret = array();

        for($i = 0; $i < count($primayKey); $i++) {
            if(!isset($primayKey[$i])) {
                throw new \Aliyun\OTS\OTSClientException("primarykeys must be an array, not a map.");
            }
            $pk = $primayKey[$i];
            if(count($pk) == 2) {
                $value = $this->preprocessPrimaryKeyValue($pk[1], null);
            } else if(count($pk) >= 3) {
                $value = $this->preprocessPrimaryKeyValue($pk[1], $pk[2]);
            }
            $ret[$i] = array(
                'name' => $pk[0],
                'value' => $value
            );
        }
        return $ret;
    }

    private function preprocessColumns($columns)
    {
        $ret = array();

        foreach ($columns as $column)
        {
            if(is_array($column)) {
                $data = array();
                if(isset($column[0])) {
                    $data['name'] = $column[0];
                }
                if(isset($column[1])) {
                    $data['value'] = $column[1];
                }
                if(isset($column[2])) {
                    $data['type'] = $column[2];
                }
                if(isset($column[3])) {
                    $data['timestamp'] = $column[3];
                }
                $columValue = array(
                    'name' => $data['name'],
                    'value' => $this->preprocessColumnValue($data),
                );
                $ret[] = $columValue;
            }else {
                throw new \Aliyun\OTS\OTSClientException("a column value must be an array");
            }
        }

        return $ret;
    }


    private function preprocessReturnContent($content)
    {
        $ret = array();

        $ret['return_type'] = $this->preprocessReturnType($content['return_type']);
        $ret['return_column_names'] = [];
        if (isset($content['return_column_names']) && is_array($content['return_column_names'])) {
            $ret['return_column_names'] = $content['return_column_names'];
        }

        return $ret;
    }
    
    private function preprocessLogicalOperator($logical_operator)
    {
    	if ( !is_int($logical_operator) ||
    	        ( $logical_operator != LogicalOperatorConst::CONST_AND && $logical_operator != LogicalOperatorConst::CONST_OR && $logical_operator != LogicalOperatorConst::CONST_NOT ) )
    	    throw new \Aliyun\OTS\OTSClientException("LogicalOperator must be one of 'LogicalOperatorConst::CONST_AND', 'LogicalOperatorConst::CONST_OR' or 'LogicalOperatorConst::CONST_NOT'.");

    	return $logical_operator;
    }
    
    private function preprocessComparatorType($comparator_type)
    {
    	if ( !is_int($comparator_type) ||
    	        ( $comparator_type != ComparatorTypeConst::CONST_EQUAL &&
    	                $comparator_type != ComparatorTypeConst::CONST_NOT_EQUAL &&
    	                $comparator_type != ComparatorTypeConst::CONST_GREATER_THAN &&
    	                $comparator_type != ComparatorTypeConst::CONST_GREATER_EQUAL &&
    	                $comparator_type != ComparatorTypeConst::CONST_LESS_THAN &&
    	                $comparator_type != ComparatorTypeConst::CONST_LESS_EQUAL ) )
    	    throw new \Aliyun\OTS\OTSClientException("Comparator must be one of 'ComparatorTypeConst::CONST_EQUAL', 'ComparatorTypeConst::CONST_NOT_EQUAL', 'ComparatorTypeConst::CONST_LESS_THAN', 'ComparatorTypeConst::CONST_LESS_EQUAL', 'ComparatorTypeConst::CONST_GREATER_THAN' or 'ComparatorTypeConst::CONST_GREATER_EQUAL'.");

    	return $comparator_type;
    }
    
    private function preprocessRowExistence($condition)
    {
    	$value=null;
    	if ( strcmp($condition, RowExistenceExpectationConst::CONST_IGNORE) == 0 )
    		$value = RowExistenceExpectation::IGNORE;
    	else if ( strcmp($condition, RowExistenceExpectationConst::CONST_EXPECT_EXIST) == 0 )
    		$value = RowExistenceExpectation::EXPECT_EXIST;
    	else if ( strcmp($condition, RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST) == 0 )
    		$value = RowExistenceExpectation::EXPECT_NOT_EXIST;
    	else {
    		throw new \Aliyun\OTS\OTSClientException("Condition must be one of 'RowExistenceExpectationConst::CONST_IGNORE', 'RowExistenceExpectationConst::CONST_EXPECT_EXIST' or 'RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST'.");
    	}
    	return $value;
    }

    /**
     * @param $operationType
     * @return int|null
     * @throws OTS\OTSClientException
     */
    private function preprocessOperationType($operationType)
    {
        $value=null;
        if ( strcmp($operationType, OperationTypeConst::CONST_PUT) == 0 )
            $value = OperationType::PUT;
        else if ( strcmp($operationType, OperationTypeConst::CONST_UPDATE) == 0 )
            $value = OperationType::UPDATE;
        else if ( strcmp($operationType, OperationTypeConst::CONST_DELETE) == 0 )
            $value = OperationType::DELETE;
        else {
            throw new \Aliyun\OTS\OTSClientException("OperationType must be one of 'OperationType::CONST_PUT', 'OperationType::CONST_UPDATE' or 'OperationType::CONST_DELETE'.");
        }
        return $value;
    }

    private function preprocessPagination($value)
    {
        $ret = array();
        if(isset($value['offset']) && is_int($value['offset'])) {
            $ret['offset'] = $value['offset'];
        }
        if(isset($value['limit']) && is_int($value['limit'])) {
            $ret['limit'] = $value['limit'];
        }
        return $ret;
    }
    
    private function preprocessColumnCondition($column_filters)
    {
    	$ret = array();

    	foreach ($column_filters as $name => $value)
    	{
    		if ( strcmp( $name, 'logical_operator' ) == 0 || strcmp( $name, 'sub_conditions' ) == 0 || strcmp( $name, 'sub_filters' ) == 0 ) {
    			// a composite condition
    			if ( strcmp( $name, 'logical_operator' ) == 0 ) {
    				$value = $this->preprocessLogicalOperator($value);
    				$ret = array_merge( $ret, array( $name => $value ) );
    			} else if ( strcmp( $name, 'sub_conditions' ) == 0 || strcmp( $name, 'sub_filters' ) == 0) {
    				$sub_conditions = array();
    				foreach( $value as $cond ) {
    					if ( is_array( $cond ) )
    						array_push( $sub_conditions, $this->preprocessColumnCondition( $cond ) );
    					else
    						throw new \Aliyun\OTS\OTSClientException( "The value of sub_conditions field should be array of array." );
    				}
    				$ret = array_merge( $ret, array( 'sub_conditions' => $sub_conditions ) );
    			}
    		} else if ( strcmp( $name, 'column_name' ) == 0 || strcmp( $name, 'value' ) == 0 || strcmp( $name, 'comparator' ) == 0 || strcmp( $name, 'pass_if_missing') == 0 || strcmp( $name, 'latest_version_only' ) == 0 ) {
    			// a relation condition
    			if ( strcmp( $name, 'value' ) == 0 ) {
    			    if(is_array($value)) {
    			        $value = array(
    			          'value' => $value[0],
                          'type' => $value[1]
                        );
                    }
    				$ret = array_merge( $ret, array(
    						$name => $this->preprocessColumnValue( $value )
    				) );
    			} else if ( strcmp( $name, 'comparator' ) == 0 ) {
    				$ret = array_merge( $ret, array(
    						$name => $this->preprocessComparatorType( $value ) ) );
    			} else {
                    $ret = array_merge($ret, array($name => $value));
                }
    		} else if(strcmp($name, 'column_pagination') == 0) {
    		    // a column pagination
    		    $ret = array_merge($ret, array(
    		        $name => $this->preprocessPagination($value)
                ));
            }
    		else {
                throw new \Aliyun\OTS\OTSClientException("Invalid argument name in column filter -" . $name);
            }
    	}

    	return $ret;
    }

    private function preprocessCondition($condition)
    {
    	$res = null;
    	if ( is_string($condition) ) {

    		$value = $this->preprocessRowExistence($condition);
	        $res = array( 'row_existence' => $value );
    	} else if ( is_array( $condition ) ) {
    		if ( isset($condition['row_existence']) && !empty($condition['row_existence']) ) {

    			$value = $this->preprocessRowExistence($condition['row_existence']);
    			$res = array( 'row_existence' => $value );
    			if ( isset($condition['column_filter']) ) {
    				$res = array_merge( $res, array( 'column_filter' => $this->preprocessColumnCondition($condition['column_filter']) ) );
    			}
                if ( isset($condition['column_condition']) ) {
                    $res = array_merge( $res, array( 'column_filter' => $this->preprocessColumnCondition($condition['column_condition']) ) );
                }
    		} else
    			throw new \Aliyun\OTS\OTSClientException("Row existence is compulsory for Condition.");
    	}

    	return $res;
    }

    private function preprocessDeleteRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['condition'] = $this->preprocessCondition($request['condition']);
        if($ret['condition']['row_existence'] == RowExistenceExpectation::EXPECT_NOT_EXIST) {
            throw new \Aliyun\OTS\OTSClientException("row_existence should be RowExistenceExpectationConst::CONST_IGNORE' or 'RowExistenceExpectationConst::CONST_EXPECT_EXIST'");
        }
        $ret['primary_key'] = $this->preprocessPrimaryKey($request['primary_key']);
        if (isset($request['return_content'])) {
            $ret['return_content'] = $this->preprocessReturnContent($request['return_content']);
        }
        if(isset($request['transaction_id'])) {
            $ret['transaction_id'] = $request['transaction_id'];
        }
        return $ret;
    }

    private function preprocessCreateTableRequest($request)
    {
        $ret = array();
        $ret['table_meta']['table_name'] = $request['table_meta']['table_name'];
        $ret['reserved_throughput'] = $request['reserved_throughput'];

        $primaryKeys = $request['table_meta']['primary_key_schema'];
        for ($i = 0; $i < count($primaryKeys); $i++) {
            if(isset($primaryKeys[$i][0])) {
                $ret['table_meta']['primary_key_schema'][$i]['name'] = $primaryKeys[$i][0];
            }
            if(isset($primaryKeys[$i][1])) {
                $ret['table_meta']['primary_key_schema'][$i]['type'] = $this->preprocessPrimaryKeyType($primaryKeys[$i][1]);
            }
            if(isset($primaryKeys[$i][2])) {
                $ret['table_meta']['primary_key_schema'][$i]['option'] = $this->preprocessPrimaryKeyOption($primaryKeys[$i][2]);
            }
        }
        if (isset($request['table_meta']['defined_column'])) {
            $definedColumns = $request['table_meta']['defined_column'];
            for ($i = 0; $i < count($definedColumns); $i++) {
                if (isset($definedColumns[$i][0])) {
                    $ret['table_meta']['defined_column'][$i]['name'] = $definedColumns[$i][0];
                }
                if (isset($definedColumns[$i][1])) {
                    $ret['table_meta']['defined_column'][$i]['type'] = ConstMapStringToInt::DefinedColumnTypeMap($definedColumns[$i][1]);
                }
            }
        }
        if (!isset($request['table_options'])) {
            $ret['table_options'] = array(
                'time_to_live' => -1,
                'max_versions' => 1,
                'deviation_cell_version_in_sec' => 86400
            );
        } else {
            $ret['table_options'] = $request['table_options'];
        }

        if(isset($request['stream_spec'])) {
            $ret['stream_spec'] = $request['stream_spec'];
        }

        if (isset($request['index_metas'])) {
            $ret['index_metas'] = $request['index_metas'];
        }
        return $ret;
    }

    private function preprocessPutRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        if(!isset($request['condition'])) {
            $request['condition'] = RowExistenceExpectationConst::CONST_IGNORE;
        }
        $ret['condition'] = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessPrimaryKey($request['primary_key']);
        if (isset($request['return_content'])) {
            $ret['return_content'] = $request['return_content'];
        }

        if (!isset($request['attribute_columns'])) {
            $request['attribute_columns'] = array();
        }

        $ret['attribute_columns'] = $this->preprocessColumns($request['attribute_columns']);
        if (isset($request['return_content'])) {
            $ret['return_content'] = $this->preprocessReturnContent($request['return_content']);
        }

        if(isset($request['transaction_id'])) {
            $ret['transaction_id'] = $request['transaction_id'];
        }
        return $ret;
    }

    private function preprocessGetRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['primary_key'] = $this->preprocessPrimaryKey($request['primary_key']);
        if (!isset($request['columns_to_get'])) {
            $ret['columns_to_get'] = array();
        } else {
            $ret['columns_to_get'] = $request['columns_to_get'];
        }
        if (isset($request['column_filter'])) {
        	$ret['column_filter'] = $this->preprocessColumnCondition($request['column_filter']);
        }
        if(isset($request['max_versions'])) {
            $ret['max_versions'] = $request['max_versions'];
        }

        if(isset($request['start_column'])) {
            $ret['start_column'] = $request['start_column'];
        }

        if(isset($request['end_column'])) {
            $ret['end_column'] = $request['end_column'];
        }

        if(isset($request['token'])) {
            $ret['token'] = $request['token'];
        }

        if(isset($request['time_range'])) {
            $ret['time_range'] = $request['time_range'];
        }

        if(isset($request['transaction_id'])) {
            $ret['transaction_id'] = $request['transaction_id'];
        }
        return $ret;
    }

    private function preprocessPutInUpdateRowRequest($columnsToPut)
    {
        $columns = $this->preprocessColumns($columnsToPut);
        return array(UpdateTypeConst::CONST_PUT => $columns);
    }

    private function preprocessDeleteInUpdateRowRequest($columnsToDelete)
    {
        $columns = array();
        foreach($columnsToDelete as $column) {
            if(!isset($column[0]) || !isset($column[1])) {
                throw new \Aliyun\OTS\OTSClientException("column in DELETE must have name and timestamp ");
            }
            $columnData = array(
                'name' => $column[0],
                'value' => array(
                    'timestamp' => $column[1],
                    'value' => null
                )
            );
            array_push($columns, $columnData);
        }
        return array(UpdateTypeConst::CONST_DELETE => $columns);
    }

    private function preprocessDeleteAllInUpdateRowRequest($columnsToDelete)
    {
        $columns = array();
        foreach ($columnsToDelete as $columnName) {
            $columns[] = $columnName;
        }
        return array(UpdateTypeConst::CONST_DELETE_ALL => $columns);
    }

    private function preprocessIncreaseInUpdateRowRequest($columnsToIncrease)
    {
        $columns = $this->preprocessColumns($columnsToIncrease);
        return array(UpdateTypeConst::CONST_INCREMENT => $columns);
    }
    
    private function preprocessUpdateRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['condition'] = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessPrimaryKey($request['primary_key']);

        $attributeColumns = array();

        if (!empty($request['update_of_attribute_columns']['PUT'])) {
            $columnsToPut = $this->preprocessPutInUpdateRowRequest($request['update_of_attribute_columns']['PUT']);
            $attributeColumns = array_merge($attributeColumns, $columnsToPut);
        }

        if (!empty($request['update_of_attribute_columns']['DELETE'])) {
            $columnsToDelete = $this->preprocessDeleteInUpdateRowRequest($request['update_of_attribute_columns']['DELETE']);
            $attributeColumns = array_merge($attributeColumns, $columnsToDelete);
        }

        if (!empty($request['update_of_attribute_columns']['DELETE_ALL'])) {
            $columnsToDelete = $this->preprocessDeleteAllInUpdateRowRequest($request['update_of_attribute_columns']['DELETE_ALL']);
            $attributeColumns = array_merge($attributeColumns, $columnsToDelete);
        }

        if (!empty($request['update_of_attribute_columns']['INCREMENT'])) {
            $columnsToIncrease = $this->preprocessIncreaseInUpdateRowRequest($request['update_of_attribute_columns']['INCREMENT']);
            $attributeColumns = array_merge($attributeColumns, $columnsToIncrease);
        }

        $ret['attribute_columns'] = $attributeColumns;

        if (isset($request['return_content'])) {
            $ret['return_content'] = $this->preprocessReturnContent($request['return_content']);
        }

        if(isset($request['transaction_id'])) {
            $ret['transaction_id'] = $request['transaction_id'];
        }

        return $ret;
    }

    private function preprocessGetRangeRequest($request)
    {
        $ret = array();

        $ret['table_name'] = $request['table_name'];
        switch ($request['direction']) {
            case 'FORWARD':
                $ret['direction'] = Direction::FORWARD;
                break;
            case 'BACKWARD':
                $ret['direction'] = Direction::BACKWARD;
                break;
            default:
                throw new \Aliyun\OTS\OTSClientException("GetRange direction must be 'FORWARD' or 'BACKWARD'.");
        }

        if (isset($request['columns_to_get'])) {
            $ret['columns_to_get'] = $request['columns_to_get'];
        } else {
            $ret['columns_to_get'] = array();
        }

        if (isset($request['limit'])) {
            $ret['limit'] = $request['limit'];
        }
        $ret['inclusive_start_primary_key'] = $this->preprocessPrimaryKey($request['inclusive_start_primary_key']);
        $ret['exclusive_end_primary_key'] = $this->preprocessPrimaryKey($request['exclusive_end_primary_key']);
        if (isset($request['column_filter'])) {
        	$ret['column_filter'] = $this->preprocessColumnCondition($request['column_filter']);
        }
        if(isset($request['max_versions'])) {
            $ret['max_versions'] = $request['max_versions'];
        }

        if(isset($request['start_column'])) {
            $ret['start_column'] = $request['start_column'];
        }

        if(isset($request['end_column'])) {
            $ret['end_column'] = $request['end_column'];
        }

        if(isset($request['token'])) {
            $ret['token'] = $request['token'];
        }

        if(isset($request['time_range'])) {
            $ret['time_range'] = $request['time_range'];
        }
        return $ret;
    }

    private function preprocessBatchGetRowRequest($request)
    {
        $ret = array();
        if (!empty($request['tables'])) {
            for ($i = 0; $i < count($request['tables']); $i++) {
                $inTable = $request['tables'][$i];
                $outTable = array();

                $outTable['table_name'] = $inTable['table_name'];
                $outTable['primary_key'] = array();
                if(isset($inTable['primary_keys'])) {
                    for ($j = 0; $j < count($inTable['primary_keys']); $j++) {
                        $outTable['primary_key'][] = $this->preprocessPrimaryKey($inTable['primary_keys'][$j]);
                    }
                }
                if (!isset($inTable['columns_to_get'])) {
                    $outTable['columns_to_get'] = array();
                } else {
                    $outTable['columns_to_get'] = $inTable['columns_to_get'];
                }
                if (isset($inTable['column_filter'])) {
                    $outTable['column_filter'] = $this->preprocessColumnCondition($inTable['column_filter']);
                }
                if(isset($inTable['max_versions'])) {
                    $outTable['max_versions'] = $inTable['max_versions'];
                }
                if(isset($inTable['start_column'])) {
                    $outTable['start_column'] = $inTable['start_column'];
                }

                if(isset($inTable['end_column'])) {
                    $outTable['end_column'] = $inTable['end_column'];
                }

                if(isset($inTable['token'])) {
                    $outTable['token'] = $inTable['token'];
                }

                if(isset($inTable['time_range'])) {
                    $outTable['time_range'] = $inTable['time_range'];
                }

                $ret['tables'][$i] = $outTable;
            }
        }

        return $ret;
    }

    private function preprocessBatchWriteRowRequest($request)
    {
        $ret = array();
        $tables = array();
        for ($i = 0; $i < count($request['tables']); $i++) {
            $inTable = $request['tables'][$i];
            $outTable = array();
            $outTable['table_name'] = $inTable['table_name'];
            $outTable['rows'] = array();
            if (!empty($inTable['rows'])) {
                for ($a = 0; $a < count($inTable['rows']); $a++) {
                    $inRow = $inTable['rows'][$a];
                    $inRow['table_name'] = $inTable['table_name'];
                    $outRow = array();
                    if(!isset($inRow['operation_type'])) {
                        throw new \Aliyun\OTS\OTSClientException("operation_type should not be empty");
                    } else if($inRow['operation_type'] == OperationTypeConst::CONST_PUT) {
                        $outRow = $this->preprocessPutRowRequest($inRow);
                    }
                    else if($inRow['operation_type'] == OperationTypeConst::CONST_UPDATE) {
                        $outRow = $this->preprocessUpdateRowRequest($inRow);
                    }
                    else if($inRow['operation_type'] == OperationTypeConst::CONST_DELETE) {
                        $outRow = $this->preprocessDeleteRowRequest($inRow);
                    }
                    if (isset($inRow['return_content'])) {
                        $outRow['return_content'] = $this->preprocessReturnContent($inRow['return_content']);
                    }
                    $outRow['operation_type'] = $this->preprocessOperationType($inRow['operation_type']);
                    $outTable['rows'][] = $outRow;
                }
            }
            $tables[] = $outTable;
        }
        $ret['tables'] = $tables;

        if(isset($request['transaction_id'])) {
            $ret['transaction_id'] = $request['transaction_id'];
        }
        return $ret;
    }

    private function encodeListTableRequest($request)
    {
        return '';
    }
    
    private function encodeDeleteTableRequest($request)
    {
        $pbMessage = new DeleteTableRequest();
        $pbMessage->setTableName($request['table_name']);

        return $pbMessage->serializeToString();
    }

    private function encodeDescribeTableRequest($request)
    {
        $pbMessage = new DescribeTableRequest();
        $pbMessage->setTableName($request['table_name']);
                                          
        return $pbMessage->serializeToString();
    }

    private function encodeComputeSplitPointsBySizeRequest($request)
    {
        $pbMessage = new ComputeSplitPointsBySizeRequest();
        $pbMessage->setTableName($request['table_name']);
        $pbMessage->setSplitSize($request['split_size']);
        return $pbMessage->serializeToString();
    }

    private function encodeUpdateTableRequest($request)
    {
        $pbMessage = new UpdateTableRequest();
        $reservedThroughput = new ReservedThroughput();
        $capacityUnit = new CapacityUnit();
        $hasCUUpdate = false;
        $hasTOUpdate = false;
        if(!empty($request['reserved_throughput']['capacity_unit']['read'])){
            $capacityUnit->setRead($request['reserved_throughput']['capacity_unit']['read']);
            $hasCUUpdate = true;
        }
        if(!empty($request['reserved_throughput']['capacity_unit']['write'])){
            $capacityUnit->setWrite($request['reserved_throughput']['capacity_unit']['write']);
            $hasCUUpdate = true;
        }
        $reservedThroughput->setCapacityUnit($capacityUnit);

        $tableOptions = new TableOptions();
        if(!empty($request['table_options']['max_versions'])) {
            $tableOptions->setMaxVersions($request['table_options']['max_versions']);
            $hasTOUpdate = true;
        }
        if(!empty($request['table_options']['time_to_live'])) {
            $tableOptions->setTimeToLive($request['table_options']['time_to_live']);
            $hasTOUpdate = true;
        }
        if(!empty($request['table_options']['deviation_cell_version_in_sec'])) {
            $tableOptions->setDeviationCellVersionInSec($request['table_options']['deviation_cell_version_in_sec']);
            $hasTOUpdate = true;
        }
        // empty(false) will return true, so judge bool is set should use isset(bool)
        if(isset($request['table_options']['allow_update'])) {
            $tableOptions->setAllowUpdate($request['table_options']['allow_update']);
            $hasTOUpdate = true;
        }
        $pbMessage->setTableName($request['table_name']);
        if($hasCUUpdate) {
            $pbMessage->setReservedThroughput($reservedThroughput);
        }
        if($hasTOUpdate) {
            $pbMessage->setTableOptions($tableOptions);
        }

        if(!empty($request['stream_spec'])) {
            $streamSpec = new StreamSpecification();
            $streamSpec->setEnableStream($request['stream_spec']['enable_stream']);
            if($request['stream_spec']['enable_stream']) {
                $streamSpec->setExpirationTime($request['stream_spec']['expiration_time']);
            }
            $pbMessage->setStreamSpec($streamSpec);
        }

        return $pbMessage->serializeToString();
    }

    private function encodeCreateTableRequest($request)
    {
        $pbMessage = new CreateTableRequest();
        $tableMeta = new TableMeta();
        $tableMeta->setTableName($request['table_meta']['table_name']);
        if (!empty($request['table_meta']['primary_key_schema']))
        {
            for ($i=0; $i < count($request['table_meta']['primary_key_schema']); $i++)
            {
                $primaryKeySchema = new PrimaryKeySchema();
                $primaryKeySchema->setName($request['table_meta']['primary_key_schema'][$i]['name']);
                $primaryKeySchema->setType($request['table_meta']['primary_key_schema'][$i]['type']);
                if(isset($request['table_meta']['primary_key_schema'][$i]['option'])) {
                    $primaryKeySchema->setOption($request['table_meta']['primary_key_schema'][$i]['option']);
                }
                $tableMeta->getPrimaryKey()[] = $primaryKeySchema;
            }
        }
        if (!empty($request["table_meta"]["defined_column"]))
        {
            for ($i=0; $i < count($request['table_meta']['defined_column']); $i++)
            {
                $definedColumnSchema = new DefinedColumnSchema();
                $definedColumnSchema->setName($request['table_meta']['defined_column'][$i]['name']);
                $definedColumnSchema->setType($request['table_meta']['defined_column'][$i]['type']);

                $tableMeta->getDefinedColumn()[] = $definedColumnSchema;
            }
        }
         
        $reservedThroughput = new ReservedThroughput();
        $capacityUnit = new CapacityUnit();
        $capacityUnit->setRead($request['reserved_throughput']['capacity_unit']['read']);
        $capacityUnit->setWrite($request['reserved_throughput']['capacity_unit']['write']);
        $reservedThroughput->setCapacityUnit($capacityUnit);

        $tableOptions = new TableOptions();
        $tableOptions->setMaxVersions($request['table_options']['max_versions']);
        $tableOptions->setTimeToLive($request['table_options']['time_to_live']);
        $tableOptions->setDeviationCellVersionInSec($request['table_options']['deviation_cell_version_in_sec']);
        if (isset($request['table_options']['allow_update'])) {
            $tableOptions->setAllowUpdate($request['table_options']['allow_update']);
        }

        if (!empty($request["index_metas"]) && is_array($request["index_metas"])) {
            $indexMetas = array();
            foreach ($request["index_metas"] as $item) {
                $indexMeta = new IndexMeta();
                $indexMeta->setName($item["name"]);
                $indexMeta->setIndexType(IndexType::IT_GLOBAL_INDEX);//only globalIndex for now
                $indexMeta->setIndexUpdateMode(IndexUpdateMode::IUM_ASYNC_INDEX);//default for now
                $indexMeta->setPrimaryKey($item["primary_key"]);
                $indexMeta->setDefinedColumn($item["defined_column"]);

                array_push($indexMetas, $indexMeta);
            }
            $pbMessage->setIndexMetas($indexMetas);
        }

        $pbMessage->setTableMeta($tableMeta);
        $pbMessage->setReservedThroughput($reservedThroughput);
        $pbMessage->setTableOptions($tableOptions);

        if(!empty($request['stream_spec'])) {
            $streamSpec = new StreamSpecification();
            $streamSpec->setEnableStream($request['stream_spec']['enable_stream']);
            if($request['stream_spec']['enable_stream']) {
                $streamSpec->setExpirationTime($request['stream_spec']['expiration_time']);
            }
            $pbMessage->setStreamSpec($streamSpec);
        }


        return $pbMessage->serializeToString();
    }

    private function encodeColumnCondition($column_filter)
    {
    	$res = null;
    	if ( isset($column_filter['logical_operator']) && isset($column_filter['sub_conditions']) ) {
    		$compositeCondition = new CompositeColumnValueFilter();
    		$compositeCondition->setCombinator( $column_filter['logical_operator'] );
    		for ($i=0; $i < count($column_filter['sub_conditions']); $i++) {
    			$sub_cond = $column_filter['sub_conditions'][$i];
    			$compositeCondition->getSubFilters()[] = $this->encodeColumnCondition( $sub_cond );
    		}

    		$columnCondition = new Filter();
    		$columnCondition->setType( FilterType::FT_COMPOSITE_COLUMN_VALUE );
    		$columnCondition->setFilter( $compositeCondition->serializeToString() );
    		$res = $columnCondition;
    	} else if ( isset($column_filter['column_name']) && isset($column_filter['value']) && isset($column_filter['comparator']) ) {
    		$relationCondition = new SingleColumnValueFilter();
    		$relationCondition->setColumnName($column_filter['column_name']);
    		$relationCondition->setComparator($column_filter['comparator']);
            $columnValue = PlainBufferBuilder::serializeColumnValue($column_filter['value']);
            $relationCondition->setColumnValue($columnValue);
    		if ( !isset($column_filter['pass_if_missing']) ) {
                $relationCondition->setFilterIfMissing(FALSE);
            } else {
                $relationCondition->setFilterIfMissing( !$column_filter['pass_if_missing']);
            }
            if ( !isset($column_filter['latest_version_only']) ) {
                $relationCondition->setLatestVersionOnly(TRUE);
            } else {
                $relationCondition->setLatestVersionOnly($column_filter['latest_version_only']);
            }
    		$columnCondition = new Filter();
    		$columnCondition->setType( FilterType::FT_SINGLE_COLUMN_VALUE );

    		$columnCondition->setFilter( $relationCondition->serializeToString() );
    		$res = $columnCondition;
    	} else if(isset($column_filter['column_pagination'])) {
            $columnCondition = new Filter();
            $pagiNation = new ColumnPaginationFilter();
            if(isset($column_filter['column_pagination']['limit'])) {
                $pagiNation->setLimit($column_filter['column_pagination']['limit']);
            }
            if(isset($column_filter['column_pagination']['offset'])) {
                $pagiNation->setOffset($column_filter['column_pagination']['offset']);
            }
            $columnCondition->setType( FilterType::FT_COLUMN_PAGINATION );
            $columnCondition->setFilter( $pagiNation->serializeToString() );
            $res = $columnCondition;
        }
    	return $res;
    }

    private function encodeReturnContent($request)
    {
        $returnContent = new ReturnContent();
        if(isset($request['return_type'])) {
            $returnContent->setReturnType($request['return_type']);
        }
        if(isset($request['return_column_names'])) {
            $returnContent->setReturnColumnNames($request['return_column_names']);
        }
        return $returnContent;
    }

    private function encodeGetRowRequest($request)
    {
        $pbMessage = new GetRowRequest();
        $pbMessage->setTableName($request['table_name']);

        $primaryKey = PlainBufferBuilder::serializePrimaryKey($request['primary_key']);
        $pbMessage->setPrimaryKey($primaryKey);

        if (!empty($request['columns_to_get']))
        {
            for ($i = 0; $i < count($request['columns_to_get']); $i++)
            {
                $pbMessage->getColumnsToGet()[] = $request['columns_to_get'][$i];
            }
        }

        if (isset($request['column_filter'])) {
            $condition = $this->encodeColumnCondition($request['column_filter']);
            if($condition != null) {
                $pbMessage->setFilter($condition->serializeToString());
            }
        }

        if(isset($request['max_versions'])) {
            $pbMessage->setMaxVersions($request['max_versions']);
        }

        if(isset($request['start_column'])) {
            $pbMessage->setStartColumn($request['start_column']);
        }

        if(isset($request['end_column'])) {
            $pbMessage->setEndColumn($request['end_column']);
        }

        if(isset($request['token'])) {
            $pbMessage->setToken($request['token']);
        }

        if(isset($request['time_range'])) {
            $timeRange = $this->parseTimeRange($request['time_range']);
            $pbMessage->setTimeRange($timeRange);
        }

        if(isset($request['transaction_id'])) {
            $pbMessage->setTransactionId($request['transaction_id']);
        }

        return $pbMessage->serializeToString();
    }

    private function encodePutRowRequest($request)
    {
        $pbMessage = new PutRowRequest();
        $pbMessage->setTableName($request['table_name']);

        $condition = new Condition();
        $condition->setRowExistence($request['condition']['row_existence']);
        if ( isset($request['condition']['column_filter']) && !empty($request['condition']['column_filter']) ) {
            $filter = $this->encodeColumnCondition($request['condition']['column_filter']);
            $condition->setColumnCondition($filter->serializeToString());
        }
        $pbMessage->setCondition($condition);

        if(isset($request['return_content'])) {
            $pbMessage->setReturnContent($this->encodeReturnContent($request['return_content']));
        }

        $row = PlainBufferBuilder::serializeForPutRow($request['primary_key'], $request['attribute_columns']);
        $pbMessage->setRow($row);

        if(isset($request['transaction_id'])) {
            $pbMessage->setTransactionId($request['transaction_id']);
        }

        return $pbMessage->serializeToString();
    }

    private function encodeUpdateRowRequest($request)
    {
        $pbMessage = new UpdateRowRequest();
        $pbMessage->setTableName($request['table_name']);

        $condition = new Condition();
        $condition->setRowExistence($request['condition']['row_existence']);
        if ( isset($request['condition']['column_filter']) && !empty($request['condition']['column_filter']) ) {
            $filter = $this->encodeColumnCondition($request['condition']['column_filter']);
            $condition->setColumnCondition($filter->serializeToString());
        }
        $pbMessage->setCondition($condition);

        if(isset($request['return_content'])) {
            $pbMessage->setReturnContent($this->encodeReturnContent($request['return_content']));
        }
        $rowChange = PlainBufferBuilder::serializeForUpdateRow($request['primary_key'], $request['attribute_columns']);
        $pbMessage->setRowChange($rowChange);

        if(isset($request['transaction_id'])) {
            $pbMessage->setTransactionId($request['transaction_id']);
        }

        return $pbMessage->serializeToString();
    }

    private function encodeDeleteRowRequest($request)
    {
        $pbMessage = new DeleteRowRequest();
        $pbMessage->setTableName($request['table_name']);

        $primaryKey = PlainBufferBuilder::serializeForDeleteRow($request['primary_key']);
        $pbMessage->setPrimaryKey($primaryKey);

        $condition = new Condition();
        $condition->setRowExistence($request['condition']['row_existence']);
        if ( isset($request['condition']['column_filter']) && !empty($request['condition']['column_filter']) ) {
            $filter = $this->encodeColumnCondition($request['condition']['column_filter']);
            $condition->setColumnCondition($filter->serializeToString());
        }
        $pbMessage->setCondition($condition);
        if(isset($request['return_content'])) {
            $pbMessage->setReturnContent($this->encodeReturnContent($request['return_content']));
        }

        if(isset($request['transaction_id'])) {
            $pbMessage->setTransactionId($request['transaction_id']);
        }

        return $pbMessage->serializeToString();
    }

    private function encodeBatchGetRowRequest($request)
    {
        $pbMessage = new BatchGetRowRequest();

        if(!empty($request['tables'])){
            for ($m = 0; $m < count($request['tables']); $m++) {
                $tableInBatchGetRowRequest = new TableInBatchGetRowRequest();
                $table = $request['tables'][$m];

                $tableInBatchGetRowRequest->setTableName($table['table_name']);

                if (!empty($table['primary_key'])){
                    for($i = 0; $i < count($table['primary_key']); $i++){
                        $primaryKey = PlainBufferBuilder::serializePrimaryKey($table['primary_key'][$i]);
                        $tableInBatchGetRowRequest->getPrimaryKey()[] = $primaryKey;
                    }
                 }

                if (!empty($table['columns_to_get']))
                {
                    for ($i = 0; $i < count($table['columns_to_get']); $i++)
                    {
                        $tableInBatchGetRowRequest->getColumnsToGet()[] = $table['columns_to_get'][$i];
                    }
                }

                if (isset($table['column_filter'])) {
                    $condition = $this->encodeColumnCondition($table['column_filter']);
                    $tableInBatchGetRowRequest->setFilter($condition->serializeToString());
                }

                if(isset($table['max_versions'])) {
                    $tableInBatchGetRowRequest->setMaxVersions($table['max_versions']);
                }

                if(isset($table['start_column'])) {
                    $tableInBatchGetRowRequest->setStartColumn($table['start_column']);
                }

                if(isset($table['end_column'])) {
                    $tableInBatchGetRowRequest->setEndColumn($table['end_column']);
                }

                if(isset($table['token'])) {
                    $tableInBatchGetRowRequest->setToken($table['token']);
                }
                if(isset($table['time_range'])) {
                    $timeRange = $this->parseTimeRange($table['time_range']);
                    $tableInBatchGetRowRequest->setTimeRange($timeRange);
                }
                $pbMessage->getTables()[] = $tableInBatchGetRowRequest;
            }
        }
        return $pbMessage->serializeToString();
    }

    private function encodeBatchWriteRowRequest($request)
    {
        $pbMessage = new BatchWriteRowRequest();

        for ($m = 0; $m < count($request['tables']); $m++) {
            $table = $request['tables'][$m];
            $tableInBatchGetWriteRequest = new TableInBatchWriteRowRequest();

            $tableInBatchGetWriteRequest->setTableName($table['table_name']);
            $cnt = 0;
            if (!empty($table['rows'])) {
                for ($p = 0; $p < count($table['rows']); $p++) {
                    $row = $table['rows'][$p];
                    $rowItem = new RowInBatchWriteRowRequest();
                    $rowItem->setType($row['operation_type']);

                    if($row['operation_type'] == OperationType::PUT) {
                        $rowChange = PlainBufferBuilder::serializeForPutRow($row['primary_key'], $row['attribute_columns']);
                        $rowItem->setRowChange($rowChange);
                    }
                    else if($row['operation_type'] == OperationType::UPDATE) {
                        $rowChange = PlainBufferBuilder::serializeForUpdateRow($row['primary_key'], $row['attribute_columns']);
                        $rowItem->setRowChange($rowChange);
                    }
                    else if($row['operation_type'] == OperationType::DELETE) {
                        $rowChange = PlainBufferBuilder::serializeForDeleteRow($row['primary_key']);
                        $rowItem->setRowChange($rowChange);
                    }
                    $condition = new Condition();
                    $condition->setRowExistence($row['condition']['row_existence']);
                    if ( isset($row['condition']['column_filter']) ) {
                        $filter = $this->encodeColumnCondition($row['condition']['column_filter']);
                        $condition->setColumnCondition($filter->serializeToString());
                    }
                    $rowItem->setCondition($condition);
                    if(isset($row['return_content'])) {
                        $rowItem->setReturnContent($this->encodeReturnContent($row['return_content']));
                    }

                    $tableInBatchGetWriteRequest->getRows()[] = $rowItem;
                }
            }

            //整体设置
            $pbMessage->getTables()[] = $tableInBatchGetWriteRequest;
        }

        if(isset($request['transaction_id'])) {
            $pbMessage->setTransactionId($request['transaction_id']);
        }

        return $pbMessage->serializeToString();

    }

    private function encodeGetRangeRequest($request)
    {
        $pbMessage = new GetRangeRequest();
        $pbMessage->setTableName($request['table_name']);
        $pbMessage->setDirection($request['direction']);

        if (!empty($request['columns_to_get']))
        {
            for ($i = 0; $i < count($request['columns_to_get']); $i++)
            {
                $pbMessage->getColumnsToGet()[] = $request['columns_to_get'][$i];
            }
        }
        $inclusiveStartPrimaryKey = PlainBufferBuilder::serializePrimaryKey($request['inclusive_start_primary_key']);
        $pbMessage->setInclusiveStartPrimaryKey($inclusiveStartPrimaryKey);

        $exclusiveEndPrimaryKey = PlainBufferBuilder::serializePrimaryKey($request['exclusive_end_primary_key']);
        $pbMessage->setExclusiveEndPrimaryKey($exclusiveEndPrimaryKey);

        if (isset($request['column_filter'])) {
            $condition = $this->encodeColumnCondition($request['column_filter']);
            $pbMessage->setFilter($condition->serializeToString());
        }

        if (isset($request['limit'])) {
            $pbMessage->setLimit($request['limit']);
        }

        if(isset($request['max_versions'])) {
            $pbMessage->setMaxVersions($request['max_versions']);
        }

        if(isset($request['start_column'])) {
            $pbMessage->setStartColumn($request['start_column']);
        }

        if(isset($request['end_column'])) {
            $pbMessage->setEndColumn($request['end_column']);
        }

        if(isset($request['token'])) {
            $pbMessage->setToken($request['token']);
        }

        if(isset($request['time_range'])) {
            $timeRange = $this->parseTimeRange($request['time_range']);
            $pbMessage->setTimeRange($timeRange);
        }

        return $pbMessage->serializeToString();

    }

    private function encodeListStreamRequest($request)
    {
        $pbMessage = new ListStreamRequest();
        $pbMessage->setTableName($request['table_name']);

        return $pbMessage->serializeToString();
    }

    private function encodeDescribeStreamRequest($request)
    {
        $pbMessage = new DescribeStreamRequest();
        $pbMessage->setStreamId($request['stream_id']);
        if(isset($request['inclusive_start_shard_id'])) {
            $pbMessage->setInclusiveStartShardId($request['inclusive_start_shard_id']);
        }
        if(isset($request['shard_limit'])) {
            $pbMessage->setShardLimit($request['shard_limit']);
        }
        return $pbMessage->serializeToString();
    }

    private function encodeGetShardIteratorRequest($request)
    {
        $pbMessage = new GetShardIteratorRequest();
        $pbMessage->setStreamId($request['stream_id']);
        $pbMessage->setShardId($request['shard_id']);
        if(!empty($request['timestamp'])) {
            $pbMessage->setTimestamp($request['timestamp']);
        }
        return $pbMessage->serializeToString();
    }

    private function encodeGetStreamRecordRequest($request)
    {
        $pbMessage = new GetStreamRecordRequest();
        $pbMessage->setShardIterator($request['shard_iterator']);
        if(!empty($request['limit'])) {
            $pbMessage->setLimit($request['limit']);
        }
        return $pbMessage->serializeToString();
    }

    private function encodeListSearchIndexRequest($request)
    {
        $pbMessage = new ListSearchIndexRequest();
        $pbMessage->setTableName($request["table_name"]);

        return $pbMessage->serializeToString();
    }

    private function encodeDescribeSearchIndexRequest($request)
    {
        $pbMessage = new DescribeSearchIndexRequest();
        $pbMessage->setTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);

        return $pbMessage->serializeToString();
    }

    private function encodeCreateSearchIndexRequest($request)
    {
        $pbMessage = new CreateSearchIndexRequest();
        $pbMessage->setTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);
        $pbMessage->setSchema($this->parseIndexSchema($request["schema"]));
        if (isset($request["source_index_name"])) {
            $pbMessage->setSourceIndexName($request["source_index_name"]);
        }
        if (isset($request["time_to_live"])) {
            $pbMessage->setTimeToLive($request["time_to_live"]);
        }

        return $pbMessage->serializeToString();
    }

    private function parseIndexSchema($schema) {
        $indexSchema = new IndexSchema();
        $fieldSchemas = array();
        for ($i = 0; $i < sizeof($schema["field_schemas"]); $i++)
        {
            $fieldSchema = $this->parseFieldSchema($schema["field_schemas"][$i]);
            array_push($fieldSchemas, $fieldSchema);
        }
        $indexSchema->setFieldSchemas($fieldSchemas);
        if (isset($schema["index_setting"])) {
            $indexSchema->setIndexSetting($this->parseIndexSetting($schema["index_setting"]));
        } else {
            $indexSchema->setIndexSetting($this->parseIndexSetting(null));
        }
        if (isset($schema["index_sort"]) && is_array($schema["index_sort"])) {
            $indexSchema->setIndexSort($this->parseSort($schema["index_sort"]));
        }

        return $indexSchema;
    }

    private function parseAnalyzerParameter($schema) {
        if (!empty($schema["analyzer"]) && !empty($schema["analyzer_parameter"])) {
            if ("single_word" == $schema["analyzer"]) {
                $param = new SingleWordAnalyzerParameter();
                $param->setCaseSensitive($schema["analyzer_parameter"]["case_sensitive"]);
                $param->setDelimitWord($schema["analyzer_parameter"]["delimit_word"]);
                return $param->serializeToString();
            }
            if ("split" == $schema["analyzer"]) {
                $param = new SplitAnalyzerParameter();
                $param->setDelimiter($schema["analyzer_parameter"]["delimiter"]);
                return $param->serializeToString();
            }
            if ("fuzzy" == $schema["analyzer"]) {
                $param = new FuzzyAnalyzerParameter();
                $param->setMaxChars($schema["analyzer_parameter"]["max_chars"]);
                $param->setMinChars($schema["analyzer_parameter"]["min_chars"]);
                return $param->serializeToString();
            }
        }
        return null;
    }

    private function parseFieldSchema($schema) {
        $fieldType = ConstMapStringToInt::FieldTypeMap($schema["field_type"]);
        $fieldSchema = new FieldSchema();
        $fieldSchema->setFieldName($schema["field_name"]);
        $fieldSchema->setFieldType($fieldType);
        if (!empty($schema["index_options"])) {
            $fieldSchema->setIndexOptions(ConstMapStringToInt::IndexOptionsMap($schema["index_options"]));
        }
        if (!empty($schema["analyzer"])) {
            $fieldSchema->setAnalyzer($schema["analyzer"]);
            $fieldSchema->setAnalyzerParameter($this->parseAnalyzerParameter($schema));
        }
        if ($fieldType == FieldType::NESTED) {
            $subFieldSchemas = array();
            for ($i = 0; $i < sizeof($schema["field_schemas"]); $i++) {
                $subFieldSchema = $this->parseFieldSchema($schema["field_schemas"][$i]);
                array_push($subFieldSchemas, $subFieldSchema);
            }
            $fieldSchema->setFieldSchemas($subFieldSchemas);

        }
        if (!empty($schema["enable_sort_and_agg"])) {
            $fieldSchema->setDocValues($schema["enable_sort_and_agg"]);
        }
        if (!empty($schema["index"])) {
            $fieldSchema->setIndex($schema["index"]);
        }
        if (!empty($schema["store"])) {
            $fieldSchema->setStore($schema["store"]);
        }
        if (!empty($schema["is_array"])) {
            $fieldSchema->setIsArray($schema["is_array"]);
        }
        if (!empty($schema["is_virtual_field"])) {
            $fieldSchema->setIsVirtualField($schema["is_array"]);
            if (!empty($schema["source_field_names"])) {
                $fieldSchema->setSourceFieldNames($schema["source_field_names"]);
            }
        }
        if (!empty($schema["date_formats"])) {
            $fieldSchema->setDateFormats($schema["date_formats"]);
        }

        return $fieldSchema;
    }

    private function parseIndexSetting($setting) {
        $indexSetting = new IndexSetting();
        $indexSetting->setNumberOfShards(1);

        if ($setting == null) {
            return $indexSetting;
        } else {
            if (isset($setting["routing_fields"]) && is_array($setting["routing_fields"])) {
                $indexSetting->setRoutingFields($setting["routing_fields"]);
            }
//            if (isset($setting["routing_partition_size"])) {
//                $indexSetting->setRoutingPartitionSize($setting["routing_partition_size"]);
//            }
        }

        return $indexSetting;
    }

    private function parseSort($sorters)
    {
        $indexSort = new Sort();
        $sorterList = array();
        for ($i = 0; $i < count($sorters); $i++) {
            $aSorter = new Sorter();
            $sorter = $sorters[$i];

            if (isset($sorter["field_sort"])) {
                $fieldSort = new FieldSort();
                $fieldSort->setFieldName($sorter["field_sort"]["field_name"]);
                if (isset($sorter["field_sort"]["order"])) {
                    $order = ConstMapStringToInt::SortOrderMap($sorter["field_sort"]["order"]);
                    $fieldSort->setOrder($order);
                }
                if (isset($sorter["field_sort"]["mode"])) {
                    $mode = ConstMapStringToInt::SortModeMap($sorter["field_sort"]["mode"]);
                    $fieldSort->setMode($mode);
                }
                if (isset($sorter["field_sort"]["nested_filter"])) {
                    $nestedFilter = new OTS\ProtoBuffer\Protocol\NestedFilter();
                    $nestedFilter->setPath($sorter["field_sort"]["nested_filter"]["path"]);
                    $nestedFilter->setFilter($this->parseQuery($sorter["field_sort"]["nested_filter"]['query']));

                    $fieldSort->setNestedFilter($nestedFilter);
                }

                $aSorter->setFieldSort($fieldSort);
            } else if (isset($sorter["pk_sort"])) {
                $pkSort = new PrimaryKeySort();
                if (isset($sorter["pk_sort"]["order"])) {
                    $order = ConstMapStringToInt::SortOrderMap($sorter["pk_sort"]["order"]);
                    $pkSort->setOrder($order);
                }

                $aSorter->setPkSort($pkSort);
            } else if (isset($sorter["geo_distance_sort"])) {
                $geoDistanceSort = new GeoDistanceSort();
                $geoDistanceSort->setFieldName($sorter["geo_distance_sort"]["field_name"]);
                if (isset($sorter["geo_distance_sort"]["order"])) {
                    $order = ConstMapStringToInt::SortOrderMap($sorter["geo_distance_sort"]["order"]);
                    $geoDistanceSort->setOrder($order);
                }
                if (isset($sorter["geo_distance_sort"]["mode"])) {
                    $mode = ConstMapStringToInt::SortModeMap($sorter["geo_distance_sort"]["mode"]);
                    $geoDistanceSort->setMode($mode);
                }
                if (isset($sorter["geo_distance_sort"]["distance_type"])) {
                    $distanceType = ConstMapStringToInt::GeoDistanceTypeMap($sorter["geo_distance_sort"]["distance_type"]);
                    $geoDistanceSort->setDistanceType($distanceType);
                }
                if (isset($sorter["geo_distance_sort"]["points"]) && is_array($sorter["geo_distance_sort"]["points"])) {
                    $points = $sorter["geo_distance_sort"]["points"];
                    $geoDistanceSort->setPoints($points);
                }
                if (isset($sorter["geo_distance_sort"]["nested_filter"])) {
                    $nestedFilter = new OTS\ProtoBuffer\Protocol\NestedFilter();
                    $nestedFilter->setPath($sorter["geo_distance_sort"]["nested_filter"]["path"]);
                    $nestedFilter->setFilter($this->parseQuery($sorter["geo_distance_sort"]["nested_filter"]));

                    $geoDistanceSort->setNestedFilter($nestedFilter);
                }

                $aSorter->setGeoDistanceSort($geoDistanceSort);
            } else if (isset($sorter["score_sort"])) {
                $scoreSort = new ScoreSort();
                if (isset($sorter["score_sort"]["order"])) {
                    $order = ConstMapStringToInt::SortOrderMap($sorter["score_sort"]["order"]);
                    $scoreSort->setOrder($order);
                }

                $aSorter->setScoreSort($scoreSort);
            }

            array_push($sorterList, $aSorter);
        }
        $indexSort->setSorter($sorterList);

        return $indexSort;
    }

    private function encodeDeleteSearchIndexRequest($request)
    {
        $pbMessage = new DeleteSearchIndexRequest();
        $pbMessage->setTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);

        return $pbMessage->serializeToString();
    }

    private function encodeUpdateSearchIndexRequest($request)
    {
        $pbMessage = new UpdateSearchIndexRequest();
        $pbMessage->setTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);
        $pbMessage->setTimeToLive($request["time_to_live"]);

        return $pbMessage->serializeToString();
    }

    private function encodeComputeSplitsRequest($request)
    {
        $pbMessage = new ComputeSplitsRequest();
        $pbMessage->setTableName($request["table_name"]);
        if (!empty($request["search_index_splits_options"])) {
            $searchIndexSplitsOptions = new SearchIndexSplitsOptions();
            $searchIndexSplitsOptions->setIndexName($request["search_index_splits_options"]["index_name"]);
            $pbMessage->setSearchIndexSplitsOptions($searchIndexSplitsOptions);
        }

        return $pbMessage->serializeToString();
    }

    private function parseColumnsToGet($columnsToGetParam)
    {
        $columnsToGet = new ColumnsToGet();
        $returnType = ConstMapStringToInt::ColumnReturnTypeMap($columnsToGetParam["return_type"]);
        $columnsToGet->setReturnType($returnType);
        if ($returnType == OTS\ProtoBuffer\Protocol\ColumnReturnType::RETURN_SPECIFIED) {
            $returnNames = array();
            if (isset($columnsToGetParam["return_names"]) && is_array($columnsToGetParam["return_names"])) {
                $returnNames = $columnsToGetParam["return_names"];
            }
            $columnsToGet->setColumnNames($returnNames);
        }
        return $columnsToGet;
    }

    private function parseScanQuery($scanQueryParam)
    {
        $scanQuery = new ScanQuery();
        $scanQuery->setQuery($this->parseQuery($scanQueryParam["query"]));
        $scanQuery->setLimit($scanQueryParam["limit"]);
        $scanQuery->setAliveTime($scanQueryParam["alive_time"]);
        if (!is_null($scanQueryParam["token"])) {
            $scanQuery->setToken($scanQueryParam["token"]);
        };
        $scanQuery->setCurrentParallelId($scanQueryParam["current_parallel_id"]);
        $scanQuery->setMaxParallel($scanQueryParam["max_parallel"]);

        return $scanQuery;
    }

    private function encodeParallelScanRequest($request)
    {
        $pbMessage = new ParallelScanRequest();
        $pbMessage->setTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);
        $pbMessage->setColumnsToGet($this->parseColumnsToGet($request["columns_to_get"]));
        $pbMessage->setSessionId($request["session_id"]);
        $pbMessage->setScanQuery($this->parseScanQuery($request["scan_query"]));
        $pbMessage->setTimeoutMs(isset($request["timeout_ms"]) ? $request["timeout_ms"] : 2000);

        return $pbMessage->serializeToString();
    }

    private function encodeSearchRequest($request)
    {
        $pbMessage = new SearchRequest();
        $pbMessage->setTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);

        $searchQuery = $this->parseSearchQuery($request["search_query"]);
        $pbMessage->setSearchQuery($searchQuery->serializeToString());
        if (isset($request["columns_to_get"])) {
            $columnsToGet = new ColumnsToGet();
            $returnType = ConstMapStringToInt::ColumnReturnTypeMap($request["columns_to_get"]["return_type"]);
            $columnsToGet->setReturnType($returnType);
            if ($returnType == OTS\ProtoBuffer\Protocol\ColumnReturnType::RETURN_SPECIFIED) {
                $returnNames = array();
                if (isset($request["columns_to_get"]["return_names"]) && is_array($request["columns_to_get"]["return_names"])) {
                    $returnNames = $request["columns_to_get"]["return_names"];
                }
                $columnsToGet->setColumnNames($returnNames);
            }
            $pbMessage->setColumnsToGet($columnsToGet);
        }
        if (isset($request["timeout_ms"])) {
            $pbMessage->setTimeoutMs($request["timeout_ms"]);
        }

        return $pbMessage->serializeToString();
    }

    private function parseSearchQuery($searchQuery)
    {
        $aSearchQuery = new SearchQuery();
        $aSearchQuery->setOffset($searchQuery["offset"]);
        $aSearchQuery->setLimit($searchQuery["limit"]);
        $aSearchQuery->setGetTotalCount($searchQuery["get_total_count"]);

        $query = $this->parseQuery($searchQuery["query"]);
        $aSearchQuery->setQuery($query);

        if (isset($searchQuery["sort"])) {
            $sort = $this->parseSort($searchQuery["sort"]);
            $aSearchQuery->setSort($sort);
        }
        if (isset($searchQuery["collapse"])) {
            $collapse = new Collapse();
            $collapse->setFieldName($searchQuery["collapse"]["field_name"]);
            $aSearchQuery->setCollapse($collapse);
        }
        if (isset($searchQuery["token"])) {
            $aSearchQuery->setToken($searchQuery["token"]);
        }
        // 参数嵌套两层：aggs.group_bys
        if (isset($searchQuery["aggs"]) && isset($searchQuery["aggs"]["aggs"])) {
            $aSearchQuery->setAggs($this->parseAggs($searchQuery["aggs"]));
        }
        // 参数嵌套两层：group_bys.group_bys
        if (isset($searchQuery["group_bys"]) && isset($searchQuery["group_bys"]["group_bys"])) {
            $aSearchQuery->setGroupBys($this->parseGroupBys($searchQuery["group_bys"]));
        }

        return $aSearchQuery;
    }

    private function parseAggs($aggs)
    {
        $items = array();
        foreach ($aggs["aggs"] as $agg) {
            $item = $this->parseAgg($agg);
            $items[] = $item;
        }
        $aggregations = new Aggregations();
        $aggregations->setAggs($items);
        return $aggregations;
    }

    private function parseAgg($agg)
    {
        $pbMessage = new Aggregation();
        $pbMessage->setName($agg["name"]);
        $pbMessage->setType(ConstMapStringToInt::AggregationTypeMap($agg["type"]));
        $body = $this->parseAggBody($agg["type"], $agg["body"]);
        $pbMessage->setBody($body);

        return $pbMessage;
    }

    private function parseAggBody($type, $param)
    {
        switch ($type) {
            case AggregationTypeConst::AGG_AVG:
                $body = new AvgAggregation();
                $body->setFieldName($param["field_name"]);
                if (isset($param["missing"])) {
                    $valueWithType = $this->preprocessColumnValue($param["missing"]);
                    $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                return $body->serializeToString();

            case AggregationTypeConst::AGG_MAX:
                $body = new MaxAggregation();
                $body->setFieldName($param["field_name"]);
                if (isset($param["missing"])) {
                    $valueWithType = $this->preprocessColumnValue($param["missing"]);
                    $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                return $body->serializeToString();

            case AggregationTypeConst::AGG_MIN:
                $body = new MinAggregation();
                $body->setFieldName($param["field_name"]);
                $valueWithType = $this->preprocessColumnValue($param["missing"]);
                $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                return $body->serializeToString();

            case AggregationTypeConst::AGG_SUM:
                $body = new SumAggregation();
                $body->setFieldName($param["field_name"]);
                if (isset($param["missing"])) {
                    $valueWithType = $this->preprocessColumnValue($param["missing"]);
                    $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                return $body->serializeToString();

            case AggregationTypeConst::AGG_COUNT:
                $body = new CountAggregation();
                $body->setFieldName($param["field_name"]);
                return $body->serializeToString();

            case AggregationTypeConst::AGG_DISTINCT_COUNT:
                $body = new DistinctCountAggregation();
                $body->setFieldName($param["field_name"]);
                if (isset($param["missing"])) {
                    $valueWithType = $this->preprocessColumnValue($param["missing"]);
                    $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                return $body->serializeToString();

            case AggregationTypeConst::AGG_TOP_ROWS:
                $body = new TopRowsAggregation();
                $body->setLimit($param["limit"]);
                if (isset($param["sort"]) && isset($param["sort"]["sorters"])) {
                    $body->setSort($this->parseSort($param["sort"]["sorters"]));
                }
                return $body->serializeToString();

            case AggregationTypeConst::AGG_PERCENTILES:
                $body = new PercentilesAggregation();
                $body->setFieldName($param["field_name"]);
                $body->setPercentiles($param["percentiles"]);
                if (isset($param["missing"])) {
                    $valueWithType = $this->preprocessColumnValue($param["missing"]);
                    $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                return $body->serializeToString();

            default:
                throw new \Aliyun\OTS\OTSClientException("aggs[].type must be AggregationTypeConst::XXX");
        }
    }

    private function parseGroupBys($groupBys)
    {
        $items = array();
        foreach ($groupBys["group_bys"] as $groupBy) {
            $item = $this->parseGroupBy($groupBy);
            $items[] = $item;
        }
        $pbMessage = new GroupBys();
        $pbMessage->setGroupBys($items);
        return $pbMessage;
    }

    private function parseGroupBy($groupBy)
    {
        $pbMessage = new GroupBy();
        $pbMessage->setName($groupBy["name"]);
        $pbMessage->setType(ConstMapStringToInt::GroupByTypeMap($groupBy["type"]));
        $body = $this->parseGroupByBody($groupBy["type"], $groupBy["body"]);
        $pbMessage->setBody($body);
        return $pbMessage;
    }

    private function parseGroupByBody($type, $param)
    {
        switch ($type) {
            case GroupByTypeConst::GROUP_BY_FIELD:
                $body = new GroupByField();
                $body->setFieldName($param["field_name"]);
                $body->setSize($param["size"]);
                if (isset($param["min_doc_count"])) {
                    $body->setMinDocCount($param["min_doc_count"]);
                }
                if (isset($param["sort"])) {
                    $sort = $this->parseGroupBySort($param["sort"]);
                    $body->setSort($sort);
                }
                $body = $this->addSubAggsAndGroupBysIfHas($body, $param);
                return $body->serializeToString();

            case GroupByTypeConst::GROUP_BY_RANGE:
                $body = new GroupByRange();
                $body->setFieldName($param["field_name"]);
                $body->setRanges($this->parseRanges($param["ranges"]));
                if (isset($param["sort"])) {
                    $sort = $this->parseGroupBySort($param["sort"]);
                    $body->setSort($sort);
                }
                $body = $this->addSubAggsAndGroupBysIfHas($body, $param);
                return $body->serializeToString();

            case GroupByTypeConst::GROUP_BY_FILTER:
                $body = new GroupByFilter();
                $filters = array();
                if (isset($param["filters"]) && is_array($param["filters"])) {
                    foreach ($param["filters"] as $item) {
                        $filter = $this->parseQuery($item);
                        $filters[] = $filter;
                    }
                }
                $body->setFilters($filters);
                $body = $this->addSubAggsAndGroupBysIfHas($body, $param);
                return $body->serializeToString();

            case GroupByTypeConst::GROUP_BY_GEO_DISTANCE:
                $body = new GroupByGeoDistance();
                $body->setFieldName($param["field_name"]);
                $body->setRanges($this->parseRanges($param["ranges"]));
                $origin = new GeoPoint();
                $origin->setLat($param["origin"]["lat"]);
                $origin->setLon($param["origin"]["lon"]);
                $body->setOrigin($origin);
                $body = $this->addSubAggsAndGroupBysIfHas($body, $param);
                return $body->serializeToString();

            case GroupByTypeConst::GROUP_BY_HISTOGRAM:
                $body = new GroupByHistogram();
                $body->setFieldName($param["field_name"]);
                $body->setMinDocCount($param["min_doc_count"]);
                if (isset($param["sort"])) {
                    $sort = $this->parseGroupBySort($param["sort"]);
                    $body->setSort($sort);
                }
                if (isset($param["field_range"])) {
                    $fieldRange = new FieldRange();
                    $minWithType = $this->preprocessColumnValue($param["field_range"]["min"]);
                    $fieldRange->setMin(PlainBufferBuilder::serializeSearchValue($minWithType));
                    $maxWithType = $this->preprocessColumnValue($param["field_range"]["max"]);
                    $fieldRange->setMax(PlainBufferBuilder::serializeSearchValue($maxWithType));
                    $body->setFieldRange($fieldRange);
                }
                if (isset($param["interval"])) {
                    $valueWithType = $this->preprocessColumnValue($param["interval"]);
                    $body->setInterval(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                if (isset($param["missing"])) {
                    $valueWithType = $this->preprocessColumnValue($param["missing"]);
                    $body->setMissing(PlainBufferBuilder::serializeSearchValue($valueWithType));
                }
                return $body->serializeToString();

            default:
                throw new \Aliyun\OTS\OTSClientException("group_bys[].type must be GroupByTypeConst::XXX");
        }
    }

    private function parseGroupBySort($sort)
    {
        $sorters = array();
        foreach ($sort["sorters"] as $item) {
            $sorter = $this->parseGroupBySorter($item);
            $sorters[] = $sorter;
        }
        $pbMessage = new GroupBySort();
        $pbMessage->setSorters($sorters);
        return $pbMessage;
    }

    private function parseGroupBySorter($sorter)
    {
        $groupBySorter = new GroupBySorter();
        if (isset($sorter["group_key_sort"])) {
            $pbMessage = new GroupKeySort();
            $pbMessage->setOrder(ConstMapStringToInt::SortOrderMap($sorter["group_key_sort"]["order"]));
            $groupBySorter->setGroupKeySort($pbMessage);
        } else if (isset($sorter["row_count_sort"])) {
            $pbMessage = new RowCountSort();
            $pbMessage->setOrder(ConstMapStringToInt::SortOrderMap($sorter["row_count_sort"]["order"]));
            $groupBySorter->setRowCountSort($pbMessage);
        } else if (isset($sorter["sub_agg_sort"])) {
            $pbMessage = new SubAggSort();
            $pbMessage->setOrder(ConstMapStringToInt::SortOrderMap($sorter["sub_agg_sort"]["order"]));
            $pbMessage->setSubAggName($sorter["sub_agg_sort"]["sub_agg_name"]);
            $groupBySorter->setSubAggSort($pbMessage);
        }
        return $groupBySorter;
    }

    private function addSubAggsAndGroupBysIfHas($groupBy, $param)
    {
        // 参数嵌套两层：sub_aggs.aggs
        if (isset($param["sub_aggs"]) && isset($param["sub_aggs"]["aggs"]) ) {
            $subAggs = $this->parseAggs($param["sub_aggs"]);
            $groupBy->setSubAggs($subAggs);
        }
        // 参数嵌套两层：sub_group_bys.group_bys
        if (isset($param["sub_group_bys"]) && isset($param["sub_group_bys"]["group_bys"])) {
            $subGroupBys = $this->parseGroupBys($param["sub_group_bys"]);
            $groupBy->setSubGroupBys($subGroupBys);
        }
        return $groupBy;
    }

    private function parseRanges($ranges)
    {
        $items = array();
        foreach ($ranges as $range) {
            $item = new Range();
            if (isset($range["from"])) {
                $item->setFrom($range["from"]);
            }
            if (isset($range["to"])) {
                $item->setTo($range["to"]);
            }
            $items[] = $item;
        }
        return $items;
    }

    private function parseQuery($query)
    {
        $aQuery = new Query();
        $queryType = ConstMapStringToInt::QueryTypeMap($query["query_type"]);
        $innerQuery = $this->parseInnerQuery($queryType, $queryType == QueryType::MATCH_ALL_QUERY ? null : $query["query"]);
        if (isset($query["score_mode"])) {
            $innerQuery->setScoreMode(ConstMapStringToInt::ScoreModeMap($query["score_mode"]));
        }

        $aQuery->setType($queryType);
        $aQuery->setQuery($innerQuery->serializeToString());

        return $aQuery;
    }

    private function parseInnerQuery($queryType, $query)
    {
        switch ($queryType) {
            case QueryType::MATCH_QUERY:
                $matchQuery = new OTS\ProtoBuffer\Protocol\MatchQuery();
                $matchQuery->setFieldName($query["field_name"]);
                $matchQuery->setText($query["text"]);
                if (isset($query["minimum_should_match"])) {
                    $matchQuery->setMinimumShouldMatch($query["minimum_should_match"]);
                }
                if (isset($query["operator"])) {
                    $matchQuery->setOperator(ConstMapStringToInt::QueryOperatorMap($query["operator"]));
                }
                if (isset($query["weight"])) {
                    $matchQuery->setWeight($query["weight"]);
                }

                return $matchQuery;

            case QueryType::MATCH_PHRASE_QUERY://2
                $matchPhraseQuery = new OTS\ProtoBuffer\Protocol\MatchPhraseQuery();
                $matchPhraseQuery->setFieldName($query["field_name"]);
                $matchPhraseQuery->setText($query["text"]);
                if (isset($query["weight"])) {
                    $matchPhraseQuery->setWeight($query["weight"]);
                }

                return $matchPhraseQuery;

            case QueryType::TERM_QUERY://3
                $termQuery = new OTS\ProtoBuffer\Protocol\TermQuery();
                $termQuery->setFieldName($query["field_name"]);

                $columnValue = $this->preprocessColumnValue($query["term"]);
                $termQuery->setTerm(PlainBufferBuilder::serializeColumnValue($columnValue));
                if (isset($query["weight"])) {
                    $termQuery->setWeight($query["weight"]);
                }

                return $termQuery;

            case QueryType::RANGE_QUERY://4
                $rangeQuery = new OTS\ProtoBuffer\Protocol\RangeQuery();
                $rangeQuery->setFieldName($query["field_name"]);
                if (isset($query["range_from"])) {
                    $rangeFrom = $this->preprocessColumnValue($query["range_from"]);
                    $rangeQuery->setRangeFrom(PlainBufferBuilder::serializeColumnValue($rangeFrom));
                    if (isset($query["include_lower"])) {
                        $rangeQuery->setIncludeLower($query["include_lower"]);
                    }
                }
                if (isset($query["range_to"])) {
                    $rangeTo = $this->preprocessColumnValue($query["range_to"]);
                    $rangeQuery->setRangeTo(PlainBufferBuilder::serializeColumnValue($rangeTo));
                    if (isset($query["include_upper"])) {
                        $rangeQuery->setIncludeUpper($query["include_upper"]);
                    }
                }

                return $rangeQuery;

            case QueryType::PREFIX_QUERY://5
                $prefixQuery = new OTS\ProtoBuffer\Protocol\PrefixQuery();
                $prefixQuery->setFieldName($query["field_name"]);
                $prefixQuery->setPrefix($query["prefix"]);
                if (isset($query["weight"])) {
                    $prefixQuery->setWeight($query["weight"]);
                }

                return $prefixQuery;

            case QueryType::BOOL_QUERY://6
                $boolQuery = new OTS\ProtoBuffer\Protocol\BoolQuery();
                if (isset($query["must_queries"]) && is_array($query["must_queries"])) {
                    $mustQueries = array();
                    foreach ($query["must_queries"] as $query) {
                        $aQuery = $this->parseQuery($query);
                        array_push($mustQueries, $aQuery);
                    }

                    $boolQuery->setMustQueries($mustQueries);
                } else if (isset($query["must_not_queries"]) && is_array($query["must_not_queries"])) {
                    $mustNotQueries = array();
                    foreach ($query["must_not_queries"] as $query) {
                        $aQuery = $this->parseQuery($query);
                        array_push($mustNotQueries, $aQuery);
                    }

                    $boolQuery->setMustNotQueries($mustNotQueries);
                } else if (isset($query["filter_queries"]) && is_array($query["filter_queries"])) {
                    $filterQueries = array();
                    foreach ($query["filter_queries"] as $query) {
                        $aQuery = $this->parseQuery($query);
                        array_push($filterQueries, $aQuery);
                    }

                    $boolQuery->setFilterQueries($filterQueries);
                } else if (isset($query["should_queries"]) && is_array($query["should_queries"])) {
                    $shouldQueries = array();
                    foreach ($query["should_queries"] as $singleQuery) {
                        $aQuery = $this->parseQuery($singleQuery);
                        array_push($shouldQueries, $aQuery);
                    }

                    $boolQuery->setShouldQueries($shouldQueries);
                    if (isset($query["minimum_should_match"])) {
                        $boolQuery->setMinimumShouldMatch($query["minimum_should_match"]);
                    }
                }

                return $boolQuery;

            case QueryType::CONST_SCORE_QUERY://7
                $constScoreQuery = new OTS\ProtoBuffer\Protocol\ConstScoreQuery();
                $constScoreQuery->setFilter($this->parseQuery($query["filter"]));

                return $constScoreQuery;

            case QueryType::FUNCTION_SCORE_QUERY://8
                $functionScoreQuery = new OTS\ProtoBuffer\Protocol\FunctionScoreQuery();
                $functionScoreQuery->setQuery($this->parseQuery($query["query"]));

                $fieldValueFactor = new OTS\ProtoBuffer\Protocol\FieldValueFactor();
                $fieldValueFactor->setFieldName($query["field_value_factor"]["field_name"]);
                $functionScoreQuery->setFieldValueFactor($fieldValueFactor);

                return $functionScoreQuery;

            case QueryType::NESTED_QUERY://9
                $nestedQuery = new OTS\ProtoBuffer\Protocol\NestedQuery();
                $nestedQuery->setPath($query["path"]);
                $nestedQuery->setQuery($this->parseQuery($query["query"]));
                if (isset($query["score_mode"])) {
                    $nestedQuery->setScoreMode(ConstMapStringToInt::ScoreModeMap($query["score_mode"]));
                }
                if (isset($query["weight"])) {
                    $nestedQuery->setWeight($query["weight"]);
                }

                return $nestedQuery;

            case QueryType::WILDCARD_QUERY://10
                $wildcardQuery = new OTS\ProtoBuffer\Protocol\WildcardQuery();
                $wildcardQuery->setFieldName($query["field_name"]);
                $wildcardQuery->setValue($query["value"]);
                if (isset($query["weight"])) {
                    $wildcardQuery->setWeight($query["weight"]);
                }

                return $wildcardQuery;

            case QueryType::MATCH_ALL_QUERY://11
                $matchAllQuery = new OTS\ProtoBuffer\Protocol\MatchAllQuery();

                return $matchAllQuery;

            case QueryType::GEO_BOUNDING_BOX_QUERY://12
                $geoBoundingBoxQuery = new OTS\ProtoBuffer\Protocol\GeoBoundingBoxQuery();
                $geoBoundingBoxQuery->setFieldName($query["field_name"]);
                $geoBoundingBoxQuery->setTopLeft($query["top_left"]);
                $geoBoundingBoxQuery->setBottomRight($query["bottom_right"]);

                return $geoBoundingBoxQuery;

            case QueryType::GEO_DISTANCE_QUERY://13
                $geoDistanceQuery = new OTS\ProtoBuffer\Protocol\GeoDistanceQuery();
                $geoDistanceQuery->setFieldName($query["field_name"]);
                $geoDistanceQuery->setCenterPoint($query["center_point"]);
                $geoDistanceQuery->setDistance($query["distance"]);

                return $geoDistanceQuery;

            case QueryType::GEO_POLYGON_QUERY://14
                $geoPolygonQuery = new OTS\ProtoBuffer\Protocol\GeoPolygonQuery();
                $geoPolygonQuery->setFieldName($query["field_name"]);
                $geoPolygonQuery->setPoints($query["points"]);

                return $geoPolygonQuery;

            case QueryType::TERMS_QUERY://15
                $termsQuery = new OTS\ProtoBuffer\Protocol\TermsQuery();
                $termsQuery->setFieldName($query["field_name"]);

                $terms = array();
                foreach ($query["terms"] as $term) {
                    $columnValue = $this->preprocessColumnValue($term);
                    array_push($terms, PlainBufferBuilder::serializeColumnValue($columnValue));
                }
                $termsQuery->setTerms($terms);
                if (isset($query["weight"])) {
                    $termsQuery->setWeight($query["weight"]);
                }

                return $termsQuery;

            case QueryType::EXISTS_QUERY://16
                $existsQuery = new OTS\ProtoBuffer\Protocol\ExistsQuery();
                $existsQuery->setFieldName($query["field_name"]);

                return $existsQuery;

            default:
                throw new \Aliyun\OTS\OTSClientException("query_type must be QueryTypeConst::XXX");
        }

        return null;
    }

    private function encodeCreateIndexRequest($request)
    {
        $pbMessage = new CreateIndexRequest();
        $pbMessage->setMainTableName($request["table_name"]);

        $indexMeta = new IndexMeta();
        $indexMeta->setName($request["index_meta"]["name"]);
        $indexMeta->setIndexType(IndexType::IT_GLOBAL_INDEX);//only globalIndex for now
        $indexMeta->setIndexUpdateMode(IndexUpdateMode::IUM_ASYNC_INDEX);//default for now
        $indexMeta->setPrimaryKey($request["index_meta"]["primary_key"]);
        $indexMeta->setDefinedColumn($request["index_meta"]["defined_column"]);

        $pbMessage->setIndexMeta($indexMeta);

        return $pbMessage->serializeToString();
    }

    private function encodeDropIndexRequest($request)
    {
        $pbMessage = new DropIndexRequest();
        $pbMessage->setMainTableName($request["table_name"]);
        $pbMessage->setIndexName($request["index_name"]);

        return $pbMessage->serializeToString();
    }

    private function encodeStartLocalTransactionRequest($request)
    {
        $pbMessage = new StartLocalTransactionRequest();
        $pbMessage->setTableName($request["table_name"]);

        $primaryKey = $this->preprocessPrimaryKey($request['key']);
        $pkPbMessage = PlainBufferBuilder::serializePrimaryKey($primaryKey);
        $pbMessage->setKey($pkPbMessage);

        return $pbMessage->serializeToString();
    }

    private function encodeCommitTransactionRequest($request)
    {
        $pbMessage = new CommitTransactionRequest();
        $pbMessage->setTransactionId($request["transaction_id"]);

        return $pbMessage->serializeToString();
    }

    private function encodeAbortTransactionRequest($request)
    {
        $pbMessage = new AbortTransactionRequest();
        $pbMessage->setTransactionId($request["transaction_id"]);

        return $pbMessage->serializeToString();
    }

    private function encodeSQLQueryRequest($request)
    {
        $pbMessage = new SQLQueryRequest();
        $pbMessage->setQuery($request["query"]);
        $version = SQLPayloadVersion::SQL_FLAT_BUFFERS;
        // only support user use flat buffers version
        // if (!empty($request["version"])) {
        //     $version = ConstMapStringToInt::SQLPayloadVersionMap($request["version"]);
        // }
        $pbMessage->setVersion($version);

        return $pbMessage->serializeToString();
    }

    public function handleBefore($context)
    {
        $request = $context->request;
        $apiName = $context->apiName;

        $debugLogger = $context->clientConfig->debugLogHandler;
        if ($debugLogger != null) {
            $debugLogger("$apiName Request " . json_encode($request));
        }

        $this->checkParameter($apiName, $request);

        // preprocess the request if neccessary 
        $preprocessMethod = "preprocess" . $apiName . "Request";
        if (method_exists($this, $preprocessMethod)) {
            $request = $this->$preprocessMethod($request);
        }

        $encodeMethodName = "encode" . $apiName . "Request";
        $context->requestBody = $this->$encodeMethodName($request);
    }

    public function handleAfter($context)
    {
        if ($context->otsServerException != null) {
            return;
        }
    }

    /**
     * @param $table
     * @return TimeRange
     */
    private function parseTimeRange($table)
    {
        $timeRange = new TimeRange();
        if (isset($table['start_time'])) {
            $timeRange->setStartTime($table['start_time']);
        }
        if (isset($table['end_time'])) {
            $timeRange->setEndTime($table['end_time']);
        }
        if (isset($table['specific_time'])) {
            $timeRange->setSpecificTime($table['specific_time']);
        }
        return $timeRange;
    }
}
