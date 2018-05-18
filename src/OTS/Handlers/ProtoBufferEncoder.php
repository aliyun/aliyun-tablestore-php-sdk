<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;
use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
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
use Aliyun\OTS\ProtoBuffer\Protocol\Condition;
use Aliyun\OTS\ProtoBuffer\Protocol\CreateTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DeleteRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DeleteTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\DescribeTableRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\Direction;
use Aliyun\OTS\ProtoBuffer\Protocol\Filter;
use Aliyun\OTS\ProtoBuffer\Protocol\FilterType;
use Aliyun\OTS\ProtoBuffer\Protocol\GetRangeRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\GetRowRequest;
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
use Aliyun\OTS\ProtoBuffer\Protocol\TableInBatchGetRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\TableInBatchWriteRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\TableMeta;
use Aliyun\OTS\ProtoBuffer\Protocol\TableOptions;
use Aliyun\OTS\ProtoBuffer\Protocol\TimeRange;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateRowRequest;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateTableRequest;

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

        if ($pkValue === null) {
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
    		if ( strcmp( $name, "logical_operator" ) == 0 || strcmp( $name, "sub_conditions" ) == 0 ) {
    			// a composite condition
    			if ( strcmp( $name, "logical_operator" ) == 0 ) {
    				$value = $this->preprocessLogicalOperator($value);
    				$ret = array_merge( $ret, array( $name => $value ) );
    			} else if ( strcmp( $name, "sub_conditions" ) == 0 ) {
    				$sub_conditions = array();
    				foreach( $value as $cond ) {
    					if ( is_array( $cond ) )
    						array_push( $sub_conditions, $this->preprocessColumnCondition( $cond ) );
    					else
    						throw new \Aliyun\OTS\OTSClientException( "The value of sub_conditions field should be array of array." );
    				}
    				$ret = array_merge( $ret, array( "sub_conditions" => $sub_conditions ) );
    			}
    		} else if ( strcmp( $name, "column_name" ) == 0 || strcmp( $name, "value" ) == 0 || strcmp( $name, "comparator" ) == 0 || strcmp( $name, "pass_if_missing" ) == 0 ) {
    			// a relation condition
    			if ( strcmp( $name, "value" ) == 0 ) {
    				$ret = array_merge( $ret, array(
    						$name => $this->preprocessColumnValue( $value )
    				) );
    			} else if ( strcmp( $name, "comparator" ) == 0 ) {
    				$ret = array_merge( $ret, array(
    						$name => $this->preprocessComparatorType( $value ) ) );
    			} else {
                    $ret = array_merge($ret, array($name => $value));
                }
    		} else if(strcmp($name, "column_pagination") == 0) {
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
	        $res = array( "row_existence" => $value );
    	} else if ( is_array( $condition ) ) {
    		if ( isset($condition['row_existence']) && !empty($condition['row_existence']) ) {

    			$value = $this->preprocessRowExistence($condition['row_existence']);
    			$res = array( "row_existence" => $value );
    			if ( isset($condition['column_filter']) ) {
    				$res = array_merge( $res, array( "column_filter" => $this->preprocessColumnCondition($condition['column_filter']) ) );
    			}
                if ( isset($condition['column_condition']) ) {
                    $res = array_merge( $res, array( "column_filter" => $this->preprocessColumnCondition($condition['column_condition']) ) );
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
        if (!isset($request['table_options'])) {
            $ret['table_options'] = array(
                "time_to_live" => -1,
                "max_versions" => 1,
                "deviation_cell_version_in_sec" => 86400
            );
        } else {
            $ret['table_options'] = $request['table_options'];
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
            if(is_array($request['time_range']) && count($request['time_range']) != 2) {
                throw new \Aliyun\OTS\OTSClientException("TimeRange must be array of length 2 or specify time");
            }
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
            if(!isset($column['name']) || !isset($column['timestamp'])) {
                throw new \Aliyun\OTS\OTSClientException("column in DELETE must have name and timestamp ");
            }
            $columnData = array(
                'name' => $column['name'],
                'value' => array(
                    'timestamp' => $column['timestamp'],
                    "value" => null
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

        $ret['attribute_columns'] = $attributeColumns;

        if (isset($request['return_content'])) {
            $ret['return_content'] = $this->preprocessReturnContent($request['return_content']);
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
            if(is_array($request['time_range']) && count($request['time_range']) != 2) {
                throw new \Aliyun\OTS\OTSClientException("TimeRange must be array of length 2 or specify time");
            }
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
                if(isset($inTable['rows'])) {
                    for ($j = 0; $j < count($inTable['rows']); $j++) {
                        $outTable['primary_key'][] = $this->preprocessPrimaryKey($inTable['rows'][$j]['primary_key']);
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
                if(isset($request['start_column'])) {
                    $outTable['start_column'] = $inTable['start_column'];
                }

                if(isset($request['end_column'])) {
                    $outTable['end_column'] = $inTable['end_column'];
                }

                if(isset($request['token'])) {
                    $outTable['token'] = $inTable['token'];
                }

                if(isset($request['time_range'])) {
                    $outTable['time_range'] = $inTable['time_range'];
                    if(is_array($request['time_range']) && count($inTable['time_range']) != 2) {
                        throw new \Aliyun\OTS\OTSClientException("TimeRange must be array of length 2 or specify time");
                    }
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
        return $ret;
    }

    private function encodeListTableRequest($request)
    {
        return "";
    }
    
    private function encodeDeleteTableRequest($request)
    {
        $pbMessage = new DeleteTableRequest();
        $pbMessage->setTableName($request["table_name"]);

        return $pbMessage->SerializeToString();
    }

    private function encodeDescribeTableRequest($request)
    {
        $pbMessage = new DescribeTableRequest();
        $pbMessage->setTableName($request["table_name"]);
                                          
        return $pbMessage->SerializeToString();
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
        $pbMessage->setTableName($request['table_name']);
        if($hasCUUpdate) {
            $pbMessage->setReservedThroughput($reservedThroughput);
        }
        if($hasTOUpdate) {
            $pbMessage->setTableOptions($tableOptions);
        }
        return $pbMessage->SerializeToString();
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
         
        $reservedThroughput = new ReservedThroughput();
        $capacityUnit = new CapacityUnit();
        $capacityUnit->setRead($request['reserved_throughput']['capacity_unit']['read']);
        $capacityUnit->setWrite($request['reserved_throughput']['capacity_unit']['write']);
        $reservedThroughput->setCapacityUnit($capacityUnit);

        $tableOptions = new TableOptions();
        $tableOptions->setMaxVersions($request['table_options']['max_versions']);
        $tableOptions->setTimeToLive($request['table_options']['time_to_live']);
        $tableOptions->setDeviationCellVersionInSec($request['table_options']['deviation_cell_version_in_sec']);

        $pbMessage->setTableMeta($tableMeta);
        $pbMessage->setReservedThroughput($reservedThroughput);
        $pbMessage->setTableOptions($tableOptions);

        return $pbMessage->SerializeToString();
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
    		$columnCondition->setFilter( $compositeCondition->SerializeToString() );
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

    		$columnCondition->setFilter( $relationCondition->SerializeToString() );
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
            $columnCondition->setFilter( $pagiNation->SerializeToString() );
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
            $timeRange = new TimeRange();
            if(is_array($request['time_range']) && count($request['time_range']) == 2) {
                $timeRange->setStartTime($request['time_range'][0]);
                $timeRange->setEndTime($request['time_range'][1]);
            } else {
                $timeRange->setSpecificTime($request['time_range']);
            }
            $pbMessage->setTimeRange($timeRange);
        }

        return $pbMessage->SerializeToString();
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

        return $pbMessage->SerializeToString();
    }

    private function encodeUpdateRowRequest($request)
    {
        $pbMessage = new UpdateRowRequest();
        $pbMessage->setTableName($request["table_name"]);

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

        return $pbMessage->SerializeToString();
    }

    private function encodeDeleteRowRequest($request)
    {
        $pbMessage = new DeleteRowRequest();
        $pbMessage->setTableName($request["table_name"]);

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

        return $pbMessage->SerializeToString();
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
                    $timeRange = new TimeRange();
                    if(is_array($table['time_range']) && count($table['time_range']) == 2) {
                        $timeRange->setStartTime($table['time_range'][0]);
                        $timeRange->setEndTime($table['time_range'][1]);
                    } else {
                        $timeRange->setSpecificTime($table['time_range']);
                    }
                    $tableInBatchGetRowRequest->setTimeRange($timeRange);
                }
                $pbMessage->getTables()[] = $tableInBatchGetRowRequest;
            }
        }
        return $pbMessage->SerializeToString();
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
        return $pbMessage->SerializeToString();

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
            $timeRange = new TimeRange();
            if(is_array($request['time_range']) && count($request['time_range']) == 2) {
                $timeRange->setStartTime($request['time_range'][0]);
                $timeRange->setEndTime($request['time_range'][1]);
            } else {
                $timeRange->setSpecificTime($request['time_range']);
            }
        }

        return $pbMessage->SerializeToString();

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
}
