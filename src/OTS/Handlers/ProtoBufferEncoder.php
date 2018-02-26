<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;

use CreateTableRequest;
use DeleteTableRequest;
use DescribeTableRequest;
use UpdateTableRequest;
use GetRowRequest;
use PutRowRequest;
use UpdateRowRequest;
use DeleteRowRequest;
use BatchGetRowRequest;
use BatchWriteRowRequest;
use GetRangeRequest;

use ColumnType, OperationType, Condition, Column, ColumnValue, ColumnUpdate;
use Direction, ReservedThroughput, CapacityUnit;
use TableInBatchGetRowRequest, RowInBatchGetRowRequest;
use TableInBatchWriteRowRequest;
use PutRowInBatchWriteRowRequest;
use UpdateRowInBatchWriteRowRequest;
use DeleteRowInBatchWriteRowRequest;
use Aliyun\OTS\LogicalOperatorConst;
use Aliyun\OTS\ComparatorTypeConst;
use Aliyun\OTS\RowExistenceExpectationConst;

class ProtoBufferEncoder
{
    private function checkParameter($request)
    {
        // TODO implement
    }

    private function preprocessColumnType($type)
    {
        switch ($type) {
            case 'INTEGER': return ColumnType::INTEGER;
            case 'STRING': return ColumnType::STRING;
            case 'BOOLEAN': return ColumnType::BOOLEAN;
            case 'DOUBLE': return ColumnType::DOUBLE;
            case 'BINARY': return ColumnType::BINARY;
            case 'INF_MIN': return ColumnType::INF_MIN;
            case 'INF_MAX': return ColumnType::INF_MAX;
            default:
                throw new \Aliyun\OTS\OTSClientException("Column type must be one of 'INTEGER', 'STRING', 'BOOLEAN', 'DOUBLE', 'BINARY', 'INF_MIN', or 'INF_MAX'.");
        }
    }

    private function preprocessColumnValue($columnValue)
    {
        if (is_bool($columnValue)) {

            // is_bool() is checked before is_int(), to avoid type upcasting
            $columnValue = array('type' => 'BOOLEAN', 'value' => $columnValue);

        } else if (is_int($columnValue)) {
            $columnValue = array('type' => 'INTEGER', 'value' => $columnValue);
        } else if (is_string($columnValue)) {
            $columnValue = array('type' => 'STRING', 'value' => $columnValue);
        } else if (is_double($columnValue) || is_float($columnValue)) {
            $columnValue = array('type' => 'DOUBLE', 'value' => $columnValue);
        } else if (is_array($columnValue)) {
            if (!isset($columnValue['type'])) {
                throw new \Aliyun\OTS\OTSClientException("An array column value must has 'type' field.");
            }

            if ($columnValue['type'] != 'INF_MIN' && $columnValue['type'] != 'INF_MAX' && !isset($columnValue['value'])) {
                throw new \Aliyun\OTS\OTSClientException("A column value wth type INTEGER, STRING, BOOLEAN, DOUBLE, or BINARY must has 'value' field.");
            }
        } else {
            throw new \Aliyun\OTS\OTSClientException("A column value must be a int, string, bool, double, float, or array.");
        }

        $type = $this->preprocessColumnType($columnValue['type']);
        $ret = array('type' => $type);

        switch ($type) {
            case ColumnType::INTEGER: 
                $ret['v_int'] = $columnValue['value'];
                break;
            case ColumnType::STRING: 
                $ret['v_string'] = $columnValue['value'];
                break;
            case ColumnType::BOOLEAN: 
                $ret['v_bool'] = $columnValue['value'];
                break;
            case ColumnType::DOUBLE:
                $ret['v_double'] = $columnValue['value'];
                break;
            case ColumnType::BINARY: 
                $ret['v_binary'] = $columnValue['value'];
                break;
            case ColumnType::INF_MIN:
                break;
            case ColumnType::INF_MAX:
                break;
        }

        return $ret;
    }

    private function preprocessColumns($columns)
    {
        $ret = array();

        foreach ($columns as $name => $value)
        {
            $data = array(
                'name' => $name,
                'value' => $this->preprocessColumnValue($value),
            );
            array_push($ret, $data);
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
    		$value = \RowExistenceExpectation::IGNORE;
    	else if ( strcmp($condition, RowExistenceExpectationConst::CONST_EXPECT_EXIST) == 0 )
    		$value = \RowExistenceExpectation::EXPECT_EXIST;
    	else if ( strcmp($condition, RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST) == 0 )
    		$value = \RowExistenceExpectation::EXPECT_NOT_EXIST;
    	else {
    		throw new \Aliyun\OTS\OTSClientException("Condition must be one of 'RowExistenceExpectationConst::CONST_IGNORE', 'RowExistenceExpectationConst::CONST_EXPECT_EXIST' or 'RowExistenceExpectationConst::CONST_EXPECT_NOT_EXIST'.");
    	}
    	return $value;
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
    			} else 
    				$ret = array_merge( $ret, array( $name => $value ) );
    		} else 
    			throw new \Aliyun\OTS\OTSClientException( "Invalid argument name in column filter -".$name );
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
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);
        return $ret;
    }

    private function preprocessCreateTableRequest($request)
    {
        $ret = array();
        $ret['table_meta']['table_name'] = $request['table_meta']['table_name'];
        $ret['reserved_throughput'] = $request['reserved_throughput'];
        foreach ($request['table_meta']['primary_key_schema'] as $k => $v) {
            $name[] = $k;
            $type[] = $this->preprocessColumnType($v);
        }
        for ($i = 0; $i < count($request['table_meta']['primary_key_schema']); $i++) {
            $ret['table_meta']['primary_key_schema'][$i]['name'] = $name[$i];
            $ret['table_meta']['primary_key_schema'][$i]['type'] = $type[$i];
        }
        return $ret;
    }

    private function preprocessPutRowRequest($request)
    {
        // FIXME handle BINARY type
        $ret = array();
        $ret['table_name']  = $request['table_name'];
		$ret['condition']   = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);
     
        if (!isset($request['attribute_columns'])) {
            $request['attribute_columns'] = array();
        }

        $ret['attribute_columns'] = $this->preprocessColumns($request['attribute_columns']);
        return $ret;
    }

    private function preprocessGetRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);
        if (!isset($request['columns_to_get'])) {
            $ret['columns_to_get'] = array();
        } else {
            $ret['columns_to_get'] = $request['columns_to_get'];
        }
        if (isset($request['column_filter'])) {
        	$ret['column_filter'] = $this->preprocessColumnCondition($request['column_filter']);
        }
        return $ret;
    }

    private function preprocessPutInUpdateRowRequest($columnsToPut)
    {
        $ret = array();
        foreach($columnsToPut as $name => $value) {
            $columnData = array(
                'type' => OperationType::PUT,
                'name' => $name,
                'value' => $this->preprocessColumnValue($value),
            );
            array_push($ret, $columnData);
        }
        return $ret;
    }

    private function preprocessDeleteInUpdateRowRequest($columnsToDelete)
    {
        $ret = array();
        foreach ($columnsToDelete as $columnName) {
            array_push($ret, array(
                'type' => OperationType::DELETE,
                'name' => $columnName,
            ));
        }
        return $ret;
    }
    
    private function preprocessUpdateRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['condition'] = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);

        $attributeColumns = array();

        if (!empty($request['attribute_columns_to_put'])) {
            $columnsToPut = $this->preprocessPutInUpdateRowRequest($request['attribute_columns_to_put']);
            $attributeColumns = array_merge($attributeColumns, $columnsToPut);
        }

        if (!empty($request['attribute_columns_to_delete'])) {
            $columnsToDelete = $this->preprocessDeleteInUpdateRowRequest($request['attribute_columns_to_delete']);
            $attributeColumns = array_merge($attributeColumns, $columnsToDelete);
        }

        $ret['attribute_columns'] = $attributeColumns;
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
        $ret['inclusive_start_primary_key'] = $this->preprocessColumns($request['inclusive_start_primary_key']);
        $ret['exclusive_end_primary_key'] = $this->preprocessColumns($request['exclusive_end_primary_key']);
        if (isset($request['column_filter'])) {
        	$ret['column_filter'] = $this->preprocessColumnCondition($request['column_filter']);
        }
        return $ret;
    }

    private function preprocessBatchGetRowRequest($request)
    {
        $ret = array();
        if (!empty($request['tables'])) {
            for ($i = 0; $i < count($request['tables']); $i++) {
                $ret['tables'][$i]['table_name'] = $request['tables'][$i]['table_name'];
                if (!empty($request['tables'][$i]['columns_to_get'])) {
                    $ret['tables'][$i]['columns_to_get'] = $request['tables'][$i]['columns_to_get'];
                }
                if (!empty($request['tables'][$i]['rows'])) {
                    for ($j = 0; $j < count($request['tables'][$i]['rows']); $j++) {
                        $ret['tables'][$i]['rows'][$j]['primary_key'] = $this->preprocessColumns($request['tables'][$i]['rows'][$j]['primary_key']);
                    }
                }
                if (isset($request['tables'][$i]['column_filter'])) {
                	$ret['tables'][$i]['column_filter'] = $this->preprocessColumnCondition($request['tables'][$i]['column_filter']);
                }
            }
        }

        return $ret;
    }

    private function preprocessBatchWriteRowRequest($request)
    {
        $ret = array();
        for ($i = 0; $i < count($request['tables']); $i++) {
            $ret['tables'][$i]['table_name'] = $request['tables'][$i]['table_name'];
            if (!empty($request['tables'][$i]['put_rows'])) {
                for ($a = 0; $a < count($request['tables'][$i]['put_rows']); $a++) {
                    $request['tables'][$i]['put_rows'][$a]['table_name'] = "";
                    $ret['tables'][$i]['put_rows'][$a] = $this->preprocessPutRowRequest($request['tables'][$i]['put_rows'][$a]);
                    unset($ret['tables'][$i]['put_rows'][$a]['table_name']);
                }
            }
            if (!empty($request['tables'][$i]['update_rows'])) {
                for ($b = 0; $b < count($request['tables'][$i]['update_rows']); $b++) {
                    $request['tables'][$i]['update_rows'][$b]['table_name'] = "";
                    $ret['tables'][$i]['update_rows'][$b] = $this->preprocessUpdateRowRequest($request['tables'][$i]['update_rows'][$b]);
                    unset($ret['tables'][$i]['update_rows'][$b]['table_name']);
                }
            }
            if (!empty($request['tables'][$i]['delete_rows'])) {
                for ($c = 0; $c < count($request['tables'][$i]['delete_rows']); $c++) {
                    $request['tables'][$i]['delete_rows'][$c]['table_name'] = "";
                    $ret['tables'][$i]['delete_rows'][$c] = $this->preprocessDeleteRowRequest($request['tables'][$i]['delete_rows'][$c]);
                    unset($ret['tables'][$i]['delete_rows'][$c]['table_name']);
                }
            }
        }
        return $ret;
    }

    private function encodeListTableRequest($request)
    {
        return "";
    }
    
    private function encodeDeleteTableRequest($request)
    {
        $pbMessage = new DeleteTableRequest();
        $pbMessage->set_table_name($request["table_name"]);
                                          
        return $pbMessage->SerializeToString();
    }

    private function encodeDescribeTableRequest($request)
    {
        $pbMessage = new DescribeTableRequest();
        $pbMessage->set_table_name($request["table_name"]);
                                          
        return $pbMessage->SerializeToString();
    }

    private function encodeUpdateTableRequest($request)
    {
        $pbMessage = new UpdateTableRequest();
        $reservedThroughput = new ReservedThroughput();
        $capacityUnit = new CapacityUnit();
        if(!empty($request['reserved_throughput']['capacity_unit']['read'])){
            $capacityUnit->set_read($request['reserved_throughput']['capacity_unit']['read']);
        }
        if(!empty($request['reserved_throughput']['capacity_unit']['write'])){
            $capacityUnit->set_write($request['reserved_throughput']['capacity_unit']['write']);
        }
        $reservedThroughput->set_capacity_unit($capacityUnit);
                 
        $pbMessage->set_table_name($request['table_name']);
        $pbMessage->set_reserved_throughput($reservedThroughput);
         
        return $pbMessage->SerializeToString();
    }

    private function encodeCreateTableRequest($request)
    {
        $pbMessage = new \CreateTableRequest();
        $tableMeta = new \TableMeta();
        $tableName = $tableMeta->set_table_name($request['table_meta']['table_name']);
        if (!empty($request['table_meta']['primary_key_schema']))
        {
            for ($i=0; $i < count($request['table_meta']['primary_key_schema']); $i++)
            {
                $columnSchema = new \ColumnSchema();
                $columnSchema->set_name($request['table_meta']['primary_key_schema'][$i]['name']);
                $columnSchema->set_type($request['table_meta']['primary_key_schema'][$i]['type']);
                $tableMeta->set_primary_key($i, $columnSchema);
            }
        }
         
        $reservedThroughput = new \ReservedThroughput();
        $capacityUnit = new \CapacityUnit();
        $capacityUnit->set_read($request['reserved_throughput']['capacity_unit']['read']);
        $capacityUnit->set_write($request['reserved_throughput']['capacity_unit']['write']);
        $reservedThroughput->set_capacity_unit($capacityUnit);
         
        $pbMessage->set_table_meta($tableMeta);
        $pbMessage->set_reserved_throughput($reservedThroughput);
         
        return $pbMessage->SerializeToString();
    }
    
    private function encodeColumnCondition($column_filter)
    {
    	$res = null;
    	if ( isset($column_filter['logical_operator']) && isset($column_filter['sub_conditions']) ) {
    		$compositeCondition = new \CompositeCondition();
    		$compositeCondition->set_combinator( $column_filter['logical_operator'] );
    		for ($i=0; $i < count($column_filter['sub_conditions']); $i++) {
    			$sub_cond = $column_filter['sub_conditions'][$i];
    			$compositeCondition->set_sub_conditions( $i, $this->encodeColumnCondition( $sub_cond ) );
    		}
    		
    		$columnCondition = new \ColumnCondition();
    		$columnCondition->set_type( \ColumnConditionType::CCT_COMPOSITE );
    		
    		$columnCondition->set_condition( $compositeCondition->SerializeToString() );
    		$res = $columnCondition;
    	} else if ( isset($column_filter['column_name']) && isset($column_filter['value']) && isset($column_filter['comparator']) ) {
    		$relationCondition = new \RelationCondition();
    		$relationCondition->set_column_name($column_filter['column_name']);
    		$relationCondition->set_comparator($column_filter['comparator']);
    		
    		$columnValue = new \ColumnValue();
    		$columnValue->set_type($column_filter['value']['type']);
    		switch($column_filter['value']['type']) {
    			case \ColumnType::BINARY:
    				$columnValue->set_v_binary($column_filter['value']['v_binary']);
    				break;
    			case \ColumnType::BOOLEAN:
    				$columnValue->set_v_bool($column_filter['value']['v_bool']);
    				break;
    			case \ColumnType::DOUBLE:
    				$columnValue->set_v_double($column_filter['value']['v_double']);
    				break;
    			case \ColumnType::INTEGER:
    				$columnValue->set_v_int($column_filter['value']['v_int']);
    				break;
    			case \ColumnType::STRING:
    				$columnValue->set_v_string($column_filter['value']['v_string']);
    				break;
    			default:
    				$columnValue->set_v_string($column_filter['value']['v_string']);
    		}
    		$relationCondition->set_column_value($columnValue);
    		if ( !isset($column_filter['pass_if_missing']) )
    			$relationCondition->set_pass_if_missing(TRUE);
    		else 
    			$relationCondition->set_pass_if_missing($column_filter['pass_if_missing']);
    		
    		$columnCondition = new \ColumnCondition();
    		$columnCondition->set_type( \ColumnConditionType::CCT_RELATION );
    		
    		$columnCondition->set_condition( $relationCondition->SerializeToString() );
    		$res = $columnCondition;
    	}
    	return $res;
    }

    private function encodeGetRowRequest($request)
    {
        $pbMessage = new GetRowRequest();
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
        if (!empty($request['columns_to_get']))
        {
            for ($i = 0; $i < count($request['columns_to_get']); $i++)
            {
                $pbMessage->set_columns_to_get($i, $request['columns_to_get'][$i]);
            }
        }
        if (isset($request['column_filter'])) {
        	$pbMessage->set_filter( $this->encodeColumnCondition($request['column_filter']) );
        }
         
        $pbMessage->set_table_name($request['table_name']);
        return $pbMessage->SerializeToString();
    }

    private function encodePutRowRequest($request)
    {
        $pbMessage = new PutRowRequest();
        $condition = new Condition();
        $condition->set_row_existence($request['condition']['row_existence']);
        if ( isset($request['condition']['column_filter']) )
        	$condition->set_column_condition($this->encodeColumnCondition($request['condition']['column_filter']));
         
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
         
        if (!empty($request['attribute_columns']))
        {
            for ($i=0; $i < count($request['attribute_columns']); $i++)
            {
                $attributeColumn = new Column();
                $columnValue = new ColumnValue();
                $attributeColumn->set_name($request['attribute_columns'][$i]['name']);
                $columnValue->set_type($request['attribute_columns'][$i]['value']['type']);
                switch ($request['attribute_columns'][$i]['value']['type'])
                {
                    case ColumnType::INTEGER:
                        $columnValue->set_v_int($request['attribute_columns'][$i]['value']['v_int']);
                        break;  
                    case ColumnType::STRING:
                        $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                        break;
                    case ColumnType::BOOLEAN:
                        $columnValue->set_v_bool($request['attribute_columns'][$i]['value']['v_bool']);
                        break;  
                    case ColumnType::DOUBLE:
                        $columnValue->set_v_double($request['attribute_columns'][$i]['value']['v_double']);
                        break;
                    case ColumnType::BINARY:
                        $columnValue->set_v_binary($request['attribute_columns'][$i]['value']['v_binary']);
                        break;
                    default:
                      $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                }
                $attributeColumn->set_value($columnValue);
                $pbMessage->set_attribute_columns($i, $attributeColumn);
            }
        }
         
        $pbMessage->set_table_name($request['table_name']);
        $pbMessage->set_condition($condition);
         
        return $pbMessage->SerializeToString();
    }

    private function encodeUpdateRowRequest($request)
    {
        $pbMessage = new UpdateRowRequest();
        $pbMessage->set_table_name($request["table_name"]);
        $condition = new Condition();
        $condition->set_row_existence($request['condition']['row_existence']);
        if ( isset($request['condition']['column_filter']) && !empty($request['condition']['column_filter']) )
        	$condition->set_column_condition($this->encodeColumnCondition($request['condition']['column_filter']));
        $pbMessage->set_condition($condition);
         
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
         
        if (!empty($request['attribute_columns']))
        {
            for ($i=0; $i < count($request['attribute_columns']); $i++)
            {
                $attributeColumn = new ColumnUpdate();
                $columnValue = new ColumnValue();
                $attributeColumn->set_name($request['attribute_columns'][$i]['name']);
                $attributeColumn->set_type($request['attribute_columns'][$i]['type']);
                if ($request['attribute_columns'][$i]['type'] == OperationType::DELETE)
                {
                    $pbMessage->set_attribute_columns($i, $attributeColumn);
                    continue;
                }
                 
                $columnValue->set_type($request['attribute_columns'][$i]['value']['type']);
                switch ($request['attribute_columns'][$i]['value']['type'])
                {
                    case ColumnType::INTEGER:
                        $columnValue->set_v_int($request['attribute_columns'][$i]['value']['v_int']);
                        break;  
                    case ColumnType::STRING:
                        $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                        break;
                    case ColumnType::BOOLEAN:
                        $columnValue->set_v_bool($request['attribute_columns'][$i]['value']['v_bool']);
                        break;  
                    case ColumnType::DOUBLE:
                        $columnValue->set_v_double($request['attribute_columns'][$i]['value']['v_double']);
                        break;
                    case ColumnType::BINARY:
                        $columnValue->set_v_binary($request['attribute_columns'][$i]['value']['v_binary']);
                        break;
                    default:
                      $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                }
                $attributeColumn->set_value($columnValue);
                $pbMessage->set_attribute_columns($i, $attributeColumn);
            }
        }
         
        return $pbMessage->SerializeToString();
    }

    private function encodeDeleteRowRequest($request)
    {
        $pbMessage = new DeleteRowRequest();
        $pbMessage->set_table_name($request["table_name"]);
        $condition = new Condition();
        $condition->set_row_existence($request['condition']['row_existence']);
        if ( isset($request['condition']['column_filter']) )
        	$condition->set_column_condition($this->encodeColumnCondition($request['condition']['column_filter']));
        $pbMessage->set_condition($condition);
         
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
        return $pbMessage->SerializeToString();
    }

    private function encodeBatchGetRowRequest($request)
    {
        $pbMessage = new BatchGetRowRequest();
 
        if(!empty($request['tables'])){
            for ($m = 0; $m < count($request['tables']); $m++) {
                $tableInBatchGetRowRequest = new TableInBatchGetRowRequest();
                $tableInBatchGetRowRequest->set_table_name($request['tables'][$m]['table_name']);
                if(!empty($request['tables'][$m]['rows'])){
                    for ($n = 0; $n < count($request['tables'][$m]['rows']); $n++) {
                        $rowInBatchGetRowRequest = new RowInBatchGetRowRequest();
                        for ($i = 0; $i < count($request['tables'][$m]['rows'][$n]['primary_key']); $i++) {
                            $pkColumn = new Column();
                            $columnValue = new ColumnValue();
                            $pkColumn->set_name($request['tables'][$m]['rows'][$n]['primary_key'][$i]['name']);
                            $columnValue->set_type($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['type']);
                            switch ($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['type']) {
                                case ColumnType::INTEGER:
                                    $columnValue->set_v_int($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_int']);
                                    break;
                                case ColumnType::STRING:
                                    $columnValue->set_v_string($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_string']);
                                    break;
                                case ColumnType::BOOLEAN:
                                    $columnValue->set_v_bool($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_bool']);
                                    break;
                                case ColumnType::DOUBLE:
                                    $columnValue->set_v_double($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_double']);
                                    break;
                                case ColumnType::BINARY:
                                    $columnValue->set_v_binary($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_binary']);
                                    break;
                                default:
                                    $columnValue->set_v_string($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_string']);
                            }
                            $pkColumn->set_value($columnValue);
                            $rowInBatchGetRowRequest->set_primary_key($i, $pkColumn);
                        }
                        $tableInBatchGetRowRequest->set_rows($n, $rowInBatchGetRowRequest);
                    }
                }
 
                if (!empty($request['tables'][$m]['columns_to_get'])) {
                    for ($c = 0; $c < count($request['tables'][$m]['columns_to_get']); $c++) {
                        $tableInBatchGetRowRequest->set_columns_to_get($c, $request['tables'][$m]['columns_to_get'][$c]);
                    }
                }
                
                if (isset($request['tables'][$m]['column_filter'])) {
                	$tableInBatchGetRowRequest->set_filter($this->encodeColumnCondition($request['tables'][$m]['column_filter']));
                }
                $pbMessage->set_tables($m, $tableInBatchGetRowRequest);
            }
        }
        return $pbMessage->SerializeToString();
    }

    private function encodeBatchWriteRowRequest($request)
    {

        $pbMessage = new BatchWriteRowRequest();

        for ($m = 0; $m < count($request['tables']); $m++) {
            $tableInBatchGetWriteRequest = new TableInBatchWriteRowRequest();
            $tableInBatchGetWriteRequest->set_table_name($request['tables'][$m]['table_name']);
            if (!empty($request['tables'][$m]['put_rows'])) {
                for ($p = 0; $p < count($request['tables'][$m]['put_rows']); $p++) {
                    $putRowItem = new PutRowInBatchWriteRowRequest();
                    $condition = new Condition();
                    $condition->set_row_existence($request['tables'][$m]['put_rows'][$p]['condition']['row_existence']);
                    if ( isset($request['tables'][$m]['put_rows'][$p]['condition']['column_filter']) )
                    	$condition->set_column_condition($this->encodeColumnCondition($request['tables'][$m]['put_rows'][$p]['condition']['column_filter']));
                    $putRowItem->set_condition($condition);
 
                    for ($n = 0; $n < count($request['tables'][$m]['put_rows'][$p]['primary_key']); $n++) {
                        $pkColumn = new Column();
                        $columnValue = new ColumnValue();
                        $pkColumn->set_name($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['name']);
                        $columnValue->set_type($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['type']);
                        switch ($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['type']) {
                            case ColumnType::INTEGER:
                                $columnValue->set_v_int($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_int']);
                                break;
                            case ColumnType::STRING:
                                $columnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_string']);
                                break;
                            case ColumnType::BOOLEAN:
                                $columnValue->set_v_bool($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_bool']);
                                break;
                            case ColumnType::DOUBLE:
                                $columnValue->set_v_double($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_double']);
                                break;
                            case ColumnType::BINARY:
                                $columnValue->set_v_binary($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_binary']);
                                break;
                            default:
                                $columnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_string']);
                        }
                        $pkColumn->set_value($columnValue);
                        $putRowItem->set_primary_key($n, $pkColumn);
                    }
                    if (!empty($request['tables'][$m]['put_rows'][$p]['attribute_columns'])) {
                        for ($c = 0; $c < count($request['tables'][$m]['put_rows'][$p]['attribute_columns']); $c++) {
                            $putRowAttributeColumn = new Column();
                            $putRowColumnValue = new ColumnValue();
                            $putRowAttributeColumn->set_name($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['name']);
                            $putRowColumnValue->set_type($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['type']);
                            switch ($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['type']) {
                                case ColumnType::INTEGER:
                                    $putRowColumnValue->set_v_int($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_int']);
                                    break;
                                case ColumnType::STRING:
                                    $putRowColumnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_string']);
                                    break;
                                case ColumnType::BOOLEAN:
                                    $putRowColumnValue->set_v_bool($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_bool']);
                                    break;
                                case ColumnType::DOUBLE:
                                    $putRowColumnValue->set_v_double($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_double']);
                                    break;
                                case ColumnType::BINARY:
                                    $putRowColumnValue->set_v_binary($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_binary']);
                                    break;
                                default:
                                    $putRowColumnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_string']);
                            }
                            $putRowAttributeColumn->set_value($putRowColumnValue);
                            $putRowItem->set_attribute_columns($c, $putRowAttributeColumn);
                        }
                    }
                    $tableInBatchGetWriteRequest->set_put_rows($p, $putRowItem);
                }
            }
 
            if (!empty($request['tables'][$m]['update_rows'])) {
                for ($j = 0; $j < count($request['tables'][$m]['update_rows']); $j++) {
                    $updateRowItem = new UpdateRowInBatchWriteRowRequest();
                    $condition = new Condition();
                    $condition->set_row_existence($request['tables'][$m]['update_rows'][$j]['condition']['row_existence']);
                    if ( isset($request['tables'][$m]['update_rows'][$j]['condition']['column_filter']) )
                    	$condition->set_column_condition($this->encodeColumnCondition($request['tables'][$m]['update_rows'][$j]['condition']['column_filter']));
                    $updateRowItem->set_condition($condition);
                    for ($b = 0; $b < count($request['tables'][$m]['update_rows'][$j]['primary_key']); $b++) {
                        $pkColumn = new Column();
                        $updateRowColumnValue = new ColumnValue();
                        $pkColumn->set_name($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['name']);
                        $updateRowColumnValue->set_type($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['type']);
                        switch ($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['type']) {
                            case ColumnType::INTEGER:
                                $updateRowColumnValue->set_v_int($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_int']);
                                break;
                            case ColumnType::STRING:
                                $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_string']);
                                break;
                            case ColumnType::BOOLEAN:
                                $updateRowColumnValue->set_v_bool($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_bool']);
                                break;
                            case ColumnType::DOUBLE:
                                $updateRowColumnValue->set_v_double($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_double']);
                                break;
                            case ColumnType::BINARY:
                                $updateRowColumnValue->set_v_binary($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_binary']);
                                break;
                            default:
                                $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_string']);
                        }
                        $pkColumn->set_value($updateRowColumnValue);
                        $updateRowItem->set_primary_key($b, $pkColumn);
                    }
 
                    if (!empty($request['tables'][$m]['update_rows'][$j]['attribute_columns'])) {
                        for ($i = 0; $i < count($request['tables'][$m]['update_rows'][$j]['attribute_columns']); $i++) {
                            $updateRowAttributeColumn = new ColumnUpdate();
                            $updateRowColumnValue = new ColumnValue();
                            $updateRowAttributeColumn->set_name($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['name']);
                            $updateRowAttributeColumn->set_type($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['type']);
                            if ($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['type'] == OperationType::DELETE) {
                                $updateRowItem->set_attribute_columns($i, $updateRowAttributeColumn);
                                continue;
                            }
 
                            $updateRowColumnValue->set_type($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['type']);
                            switch ($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['type']) {
                                case ColumnType::INTEGER:
                                    $updateRowColumnValue->set_v_int($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_int']);
                                    break;
                                case ColumnType::STRING:
                                    $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_string']);
                                    break;
                                case ColumnType::BOOLEAN:
                                    $updateRowColumnValue->set_v_bool($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_bool']);
                                    break;
                                case ColumnType::DOUBLE:
                                    $updateRowColumnValue->set_v_double($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_double']);
                                    break;
                                case ColumnType::BINARY:
                                    $updateRowColumnValue->set_v_binary($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_binary']);
                                    break;
                                default:
                                    $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_string']);
                            }
                            $updateRowAttributeColumn->set_value($updateRowColumnValue);
                            $updateRowItem->set_attribute_columns($i, $updateRowAttributeColumn);
                        }
                    }
                    $tableInBatchGetWriteRequest->set_update_rows($j, $updateRowItem);
                }
            }
 
            if (!empty($request['tables'][$m]['delete_rows'])) {
                for ($k = 0; $k < count($request['tables'][$m]['delete_rows']); $k++) {
                    $deleteRowItem = new DeleteRowInBatchWriteRowRequest();
                    $condition = new Condition();
                    $condition->set_row_existence($request['tables'][$m]['delete_rows'][$k]['condition']['row_existence']);
                    if ( isset($request['tables'][$m]['delete_rows'][$k]['condition']['column_filter']) )
                    	$condition->set_column_condition($this->encodeColumnCondition($request['tables'][$m]['delete_rows'][$k]['condition']['column_filter']));
                    $deleteRowItem->set_condition($condition);
                    for ($a = 0; $a < count($request['tables'][$m]['delete_rows'][$k]['primary_key']); $a++) {
                        $pkColumn = new Column();
                        $deleteRowColumnValue = new ColumnValue();
                        $pkColumn->set_name($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['name']);
                        $deleteRowColumnValue->set_type($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['type']);
                        switch ($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['type']) {
                            case ColumnType::INTEGER:
                                $deleteRowColumnValue->set_v_int($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_int']);
                                break;
                            case ColumnType::STRING:
                                $deleteRowColumnValue->set_v_string($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_string']);
                                break;
                            case ColumnType::BOOLEAN:
                                $deleteRowColumnValue->set_v_bool($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_bool']);
                                break;
                            case ColumnType::DOUBLE:
                                $deleteRowColumnValue->set_v_double($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_double']);
                                break;
                            case ColumnType::BINARY:
                                $deleteRowColumnValue->set_v_binary($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_binary']);
                                break;
                            default:
                                $deleteRowColumnValue->set_v_string($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_string']);
                        }
                        $pkColumn->set_value($deleteRowColumnValue);
                        $deleteRowItem->set_primary_key($a, $pkColumn);
                    }
                    $tableInBatchGetWriteRequest->set_delete_rows($k, $deleteRowItem);
                }
            }
            //整体设置
            $pbMessage->set_tables($m, $tableInBatchGetWriteRequest);
        }
        return $pbMessage->SerializeToString();

    }

    private function encodeGetRangeRequest($request)
    {

        $pbMessage = new GetRangeRequest();
        for ($i=0; $i < count($request['inclusive_start_primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['inclusive_start_primary_key'][$i]['name']);
            $columnValue->set_type($request['inclusive_start_primary_key'][$i]['value']['type']);
            switch ($request['inclusive_start_primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['inclusive_start_primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['inclusive_start_primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['inclusive_start_primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['inclusive_start_primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['inclusive_start_primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    if(!empty($request['inclusive_start_primary_key'][$i]['value']['v_string'])){
                            $columnValue->set_v_string($request['inclusive_start_primary_key'][$i]['value']['v_string']);
                    }
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_inclusive_start_primary_key($i, $pkColumn);
        }
        for ($i=0; $i < count($request['exclusive_end_primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['exclusive_end_primary_key'][$i]['name']);
            $columnValue->set_type($request['exclusive_end_primary_key'][$i]['value']['type']);
            switch ($request['exclusive_end_primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['exclusive_end_primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['exclusive_end_primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['exclusive_end_primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['exclusive_end_primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['exclusive_end_primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    if(!empty($request['exclusive_end_primary_key'][$i]['value']['v_string'])){
                        $columnValue->set_v_string($request['exclusive_end_primary_key'][$i]['value']['v_string']);
                    }
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_exclusive_end_primary_key($i, $pkColumn);
        }
         
        if (!empty($request['columns_to_get']))
        {
            for ($i = 0; $i < count($request['columns_to_get']); $i++)
            {
                $pbMessage->set_columns_to_get($i, $request['columns_to_get'][$i]);
            }
        }
         
        $pbMessage->set_table_name($request['table_name']);

        if (isset($request['limit'])) {
            $pbMessage->set_limit($request['limit']);
        }
        $pbMessage->set_direction($request['direction']);
        
        if (isset($request['column_filter'])) {
        	$pbMessage->set_filter( $this->encodeColumnCondition($request['column_filter']) );
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
