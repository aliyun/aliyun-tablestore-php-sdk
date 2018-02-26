<?php
class Error extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBString';
        $this->values['2'] = '';
    }
    function code() {
        return $this->_get_value ( '1' );
    }
    function set_code($value) {
        return $this->_set_value ( '1', $value );
    }
    function message() {
        return $this->_get_value ( '2' );
    }
    function set_message($value) {
        return $this->_set_value ( '2', $value );
    }
}
class ColumnType extends PBEnum {
    const INF_MIN = 0;
    const INF_MAX = 1;
    const INTEGER = 2;
    const STRING = 3;
    const BOOLEAN = 4;
    const DOUBLE = 5;
    const BINARY = 6;
}
class ColumnSchema extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'ColumnType';
        $this->values['2'] = '';
    }
    function name() {
        return $this->_get_value ( '1' );
    }
    function set_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function type() {
        return $this->_get_value ( '2' );
    }
    function set_type($value) {
        return $this->_set_value ( '2', $value );
    }
}
class ColumnValue extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ColumnType';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBInt';
        $this->values['2'] = '';
        $this->fields['3'] = 'PBString';
        $this->values['3'] = '';
        $this->fields['4'] = 'PBBool';
        $this->values['4'] = '';
        $this->fields['5'] = 'PBDouble';
        $this->values['5'] = '';
        $this->fields['6'] = 'PBString';
        $this->values['6'] = '';
    }
    function type() {
        return $this->_get_value ( '1' );
    }
    function set_type($value) {
        return $this->_set_value ( '1', $value );
    }
    function v_int() {
        return $this->_get_value ( '2' );
    }
    function set_v_int($value) {
        return $this->_set_value ( '2', $value );
    }
    function v_string() {
        return $this->_get_value ( '3' );
    }
    function set_v_string($value) {
        return $this->_set_value ( '3', $value );
    }
    function v_bool() {
        return $this->_get_value ( '4' );
    }
    function set_v_bool($value) {
        return $this->_set_value ( '4', $value );
    }
    function v_double() {
        return $this->_get_value ( '5' );
    }
    function set_v_double($value) {
        return $this->_set_value ( '5', $value );
    }
    function v_binary() {
        return $this->_get_value ( '6' );
    }
    function set_v_binary($value) {
        return $this->_set_value ( '6', $value );
    }
}
class Column extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'ColumnValue';
        $this->values['2'] = '';
    }
    function name() {
        return $this->_get_value ( '1' );
    }
    function set_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function value() {
        return $this->_get_value ( '2' );
    }
    function set_value($value) {
        return $this->_set_value ( '2', $value );
    }
}
class Row extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'Column';
        $this->values['1'] = array();
        $this->fields['2'] = 'Column';
        $this->values['2'] = array();
    }
    function primary_key_columns($offset) {
        return $this->_get_arr_value ( '1', $offset );
    }
    function add_primary_key_columns() {
        return $this->_add_arr_value ( '1' );
    }
    function set_primary_key_columns($index, $value) {
        $this->_set_arr_value ( '1', $index, $value );
    }
    function remove_last_primary_key_columns() {
        $this->_remove_last_arr_value ( '1' );
    }
    function primary_key_columns_size() {
        return $this->_get_arr_size ( '1' );
    }
    function attribute_columns($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_attribute_columns() {
        return $this->_add_arr_value ( '2' );
    }
    function set_attribute_columns($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_attribute_columns() {
        $this->_remove_last_arr_value ( '2' );
    }
    function attribute_columns_size() {
        return $this->_get_arr_size ( '2' );
    }
}
class TableMeta extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'ColumnSchema';
        $this->values['2'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '2' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '2' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '2' );
    }
}
class RowExistenceExpectation extends PBEnum {
    const IGNORE = 0;
    const EXPECT_EXIST = 1;
    const EXPECT_NOT_EXIST = 2;
}
class ColumnConditionType extends PBEnum {
    const CCT_RELATION = 1;
    const CCT_COMPOSITE = 2;
}
class ComparatorType extends PBEnum {
    const CT_EQUAL = 1;
    const CT_NOT_EQUAL = 2;
    const CT_GREATER_THAN = 3;
    const CT_GREATER_EQUAL = 4;
    const CT_LESS_THAN = 5;
    const CT_LESS_EQUAL = 6;
}
class RelationCondition extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ComparatorType';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBString';
        $this->values['2'] = '';
        $this->fields['3'] = 'ColumnValue';
        $this->values['3'] = '';
        $this->fields['4'] = 'PBBool';
        $this->values['4'] = '';
    }
    function comparator() {
        return $this->_get_value ( '1' );
    }
    function set_comparator($value) {
        return $this->_set_value ( '1', $value );
    }
    function column_name() {
        return $this->_get_value ( '2' );
    }
    function set_column_name($value) {
        return $this->_set_value ( '2', $value );
    }
    function column_value() {
        return $this->_get_value ( '3' );
    }
    function set_column_value($value) {
        return $this->_set_value ( '3', $value );
    }
    function pass_if_missing() {
        return $this->_get_value ( '4' );
    }
    function set_pass_if_missing($value) {
        return $this->_set_value ( '4', $value );
    }
}
class LogicalOperator extends PBEnum {
    const LO_NOT = 1;
    const LO_AND = 2;
    const LO_OR = 3;
}
class ColumnCondition extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ColumnConditionType';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBString';
        $this->values['2'] = '';
    }
    function type() {
        return $this->_get_value ( '1' );
    }
    function set_type($value) {
        return $this->_set_value ( '1', $value );
    }
    function condition() {
        return $this->_get_value ( '2' );
    }
    function set_condition($value) {
        return $this->_set_value ( '2', $value );
    }
}
class CompositeCondition extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'LogicalOperator';
        $this->values['1'] = '';
        $this->fields['2'] = 'ColumnCondition';
        $this->values['2'] = array();
    }
    function combinator() {
        return $this->_get_value ( '1' );
    }
    function set_combinator($value) {
        return $this->_set_value ( '1', $value );
    }
    function sub_conditions($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_sub_conditions() {
        return $this->_add_arr_value ( '2' );
    }
    function set_sub_conditions($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_sub_conditions() {
        $this->_remove_last_arr_value ( '2' );
    }
    function sub_conditions_size() {
        return $this->_get_arr_size ( '2' );
    }
}
class Condition extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'RowExistenceExpectation';
        $this->values['1'] = '';
        $this->fields['2'] = 'ColumnCondition';
        $this->values['2'] = '';
    }
    function row_existence() {
        return $this->_get_value ( '1' );
    }
    function set_row_existence($value) {
        return $this->_set_value ( '1', $value );
    }
    function column_condition() {
        return $this->_get_value ( '2' );
    }
    function set_column_condition($value) {
        return $this->_set_value ( '2', $value );
    }
}
class CapacityUnit extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBInt';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBInt';
        $this->values['2'] = '';
    }
    function read() {
        return $this->_get_value ( '1' );
    }
    function set_read($value) {
        return $this->_set_value ( '1', $value );
    }
    function write() {
        return $this->_get_value ( '2' );
    }
    function set_write($value) {
        return $this->_set_value ( '2', $value );
    }
}
class ReservedThroughputDetails extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'CapacityUnit';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBInt';
        $this->values['2'] = '';
        $this->fields['3'] = 'PBInt';
        $this->values['3'] = '';
        $this->fields['4'] = 'PBInt';
        $this->values['4'] = '';
    }
    function capacity_unit() {
        return $this->_get_value ( '1' );
    }
    function set_capacity_unit($value) {
        return $this->_set_value ( '1', $value );
    }
    function last_increase_time() {
        return $this->_get_value ( '2' );
    }
    function set_last_increase_time($value) {
        return $this->_set_value ( '2', $value );
    }
    function last_decrease_time() {
        return $this->_get_value ( '3' );
    }
    function set_last_decrease_time($value) {
        return $this->_set_value ( '3', $value );
    }
    function number_of_decreases_today() {
        return $this->_get_value ( '4' );
    }
    function set_number_of_decreases_today($value) {
        return $this->_set_value ( '4', $value );
    }
}
class ReservedThroughput extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'CapacityUnit';
        $this->values['1'] = '';
    }
    function capacity_unit() {
        return $this->_get_value ( '1' );
    }
    function set_capacity_unit($value) {
        return $this->_set_value ( '1', $value );
    }
}
class ConsumedCapacity extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'CapacityUnit';
        $this->values['1'] = '';
    }
    function capacity_unit() {
        return $this->_get_value ( '1' );
    }
    function set_capacity_unit($value) {
        return $this->_set_value ( '1', $value );
    }
}
class CreateTableRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'TableMeta';
        $this->values['1'] = '';
        $this->fields['2'] = 'ReservedThroughput';
        $this->values['2'] = '';
    }
    function table_meta() {
        return $this->_get_value ( '1' );
    }
    function set_table_meta($value) {
        return $this->_set_value ( '1', $value );
    }
    function reserved_throughput() {
        return $this->_get_value ( '2' );
    }
    function set_reserved_throughput($value) {
        return $this->_set_value ( '2', $value );
    }
}
class UpdateTableRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'ReservedThroughput';
        $this->values['2'] = '';
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function reserved_throughput() {
        return $this->_get_value ( '2' );
    }
    function set_reserved_throughput($value) {
        return $this->_set_value ( '2', $value );
    }
}
class UpdateTableResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ReservedThroughputDetails';
        $this->values['1'] = '';
    }
    function reserved_throughput_details() {
        return $this->_get_value ( '1' );
    }
    function set_reserved_throughput_details($value) {
        return $this->_set_value ( '1', $value );
    }
}
class DescribeTableRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
}
class DescribeTableResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'TableMeta';
        $this->values['1'] = '';
        $this->fields['2'] = 'ReservedThroughputDetails';
        $this->values['2'] = '';
    }
    function table_meta() {
        return $this->_get_value ( '1' );
    }
    function set_table_meta($value) {
        return $this->_set_value ( '1', $value );
    }
    function reserved_throughput_details() {
        return $this->_get_value ( '2' );
    }
    function set_reserved_throughput_details($value) {
        return $this->_set_value ( '2', $value );
    }
}
class ListTableRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
    }
    function ncvonline() {
        return $this->_get_value ( '1' );
    }
    function set_ncvonline($value) {
        return $this->_set_value ( '1', $value );
    }
}
class ListTableResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = array();
    }
    function table_names($offset) {
        $v = $this->_get_arr_value ( '1', $offset );
        return $v->get_value ();
    }
    function append_table_names($value) {
        $v = $this->_add_arr_value ( '1' );
        $v->set_value ( $value );
    }
    function set_table_names($index, $value) {
        $v = new $this->fields['1'] ();
        $v->set_value ( $value );
        $this->_set_arr_value ( '1', $index, $v );
    }
    function remove_last_table_names() {
        $this->_remove_last_arr_value ( '1' );
    }
    function table_names_size() {
        return $this->_get_arr_size ( '1' );
    }
}
class DeleteTableRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
}
class GetRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'Column';
        $this->values['2'] = array();
        $this->fields['3'] = 'PBString';
        $this->values['3'] = array();
        $this->fields['4'] = 'ColumnCondition';
        $this->values['4'] = '';
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '2' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '2' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '2' );
    }
    function columns_to_get($offset) {
        $v = $this->_get_arr_value ( '3', $offset );
        return $v->get_value ();
    }
    function append_columns_to_get($value) {
        $v = $this->_add_arr_value ( '3' );
        $v->set_value ( $value );
    }
    function set_columns_to_get($index, $value) {
        $v = new $this->fields['3'] ();
        $v->set_value ( $value );
        $this->_set_arr_value ( '3', $index, $v );
    }
    function remove_last_columns_to_get() {
        $this->_remove_last_arr_value ( '3' );
    }
    function columns_to_get_size() {
        return $this->_get_arr_size ( '3' );
    }
    function filter() {
        return $this->_get_value ( '4' );
    }
    function set_filter($value) {
        return $this->_set_value ( '4', $value );
    }
}
class GetRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ConsumedCapacity';
        $this->values['1'] = '';
        $this->fields['2'] = 'Row';
        $this->values['2'] = '';
    }
    function consumed() {
        return $this->_get_value ( '1' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '1', $value );
    }
    function row() {
        return $this->_get_value ( '2' );
    }
    function set_row($value) {
        return $this->_set_value ( '2', $value );
    }
}
class OperationType extends PBEnum {
    const PUT = 1;
    const DELETE = 2;
}
class ColumnUpdate extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'OperationType';
        $this->values['1'] = '';
        $this->fields['2'] = 'PBString';
        $this->values['2'] = '';
        $this->fields['3'] = 'ColumnValue';
        $this->values['3'] = '';
    }
    function type() {
        return $this->_get_value ( '1' );
    }
    function set_type($value) {
        return $this->_set_value ( '1', $value );
    }
    function name() {
        return $this->_get_value ( '2' );
    }
    function set_name($value) {
        return $this->_set_value ( '2', $value );
    }
    function value() {
        return $this->_get_value ( '3' );
    }
    function set_value($value) {
        return $this->_set_value ( '3', $value );
    }
}
class UpdateRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'Condition';
        $this->values['2'] = '';
        $this->fields['3'] = 'Column';
        $this->values['3'] = array();
        $this->fields['4'] = 'ColumnUpdate';
        $this->values['4'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function condition() {
        return $this->_get_value ( '2' );
    }
    function set_condition($value) {
        return $this->_set_value ( '2', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '3' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '3' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '3' );
    }
    function attribute_columns($offset) {
        return $this->_get_arr_value ( '4', $offset );
    }
    function add_attribute_columns() {
        return $this->_add_arr_value ( '4' );
    }
    function set_attribute_columns($index, $value) {
        $this->_set_arr_value ( '4', $index, $value );
    }
    function remove_last_attribute_columns() {
        $this->_remove_last_arr_value ( '4' );
    }
    function attribute_columns_size() {
        return $this->_get_arr_size ( '4' );
    }
}
class UpdateRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ConsumedCapacity';
        $this->values['1'] = '';
    }
    function consumed() {
        return $this->_get_value ( '1' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '1', $value );
    }
}
class PutRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'Condition';
        $this->values['2'] = '';
        $this->fields['3'] = 'Column';
        $this->values['3'] = array();
        $this->fields['4'] = 'Column';
        $this->values['4'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function condition() {
        return $this->_get_value ( '2' );
    }
    function set_condition($value) {
        return $this->_set_value ( '2', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '3' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '3' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '3' );
    }
    function attribute_columns($offset) {
        return $this->_get_arr_value ( '4', $offset );
    }
    function add_attribute_columns() {
        return $this->_add_arr_value ( '4' );
    }
    function set_attribute_columns($index, $value) {
        $this->_set_arr_value ( '4', $index, $value );
    }
    function remove_last_attribute_columns() {
        $this->_remove_last_arr_value ( '4' );
    }
    function attribute_columns_size() {
        return $this->_get_arr_size ( '4' );
    }
}
class PutRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ConsumedCapacity';
        $this->values['1'] = '';
    }
    function consumed() {
        return $this->_get_value ( '1' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '1', $value );
    }
}
class DeleteRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'Condition';
        $this->values['2'] = '';
        $this->fields['3'] = 'Column';
        $this->values['3'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function condition() {
        return $this->_get_value ( '2' );
    }
    function set_condition($value) {
        return $this->_set_value ( '2', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '3' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '3' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '3' );
    }
}
class DeleteRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ConsumedCapacity';
        $this->values['1'] = '';
    }
    function consumed() {
        return $this->_get_value ( '1' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '1', $value );
    }
}
class RowInBatchGetRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'Column';
        $this->values['1'] = array();
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '1', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '1' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '1', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '1' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '1' );
    }
}
class TableInBatchGetRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'RowInBatchGetRowRequest';
        $this->values['2'] = array();
        $this->fields['3'] = 'PBString';
        $this->values['3'] = array();
        $this->fields['4'] = 'ColumnCondition';
        $this->values['4'] = '';
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function rows($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_rows() {
        return $this->_add_arr_value ( '2' );
    }
    function set_rows($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_rows() {
        $this->_remove_last_arr_value ( '2' );
    }
    function rows_size() {
        return $this->_get_arr_size ( '2' );
    }
    function columns_to_get($offset) {
        $v = $this->_get_arr_value ( '3', $offset );
        return $v->get_value ();
    }
    function append_columns_to_get($value) {
        $v = $this->_add_arr_value ( '3' );
        $v->set_value ( $value );
    }
    function set_columns_to_get($index, $value) {
        $v = new $this->fields['3'] ();
        $v->set_value ( $value );
        $this->_set_arr_value ( '3', $index, $v );
    }
    function remove_last_columns_to_get() {
        $this->_remove_last_arr_value ( '3' );
    }
    function columns_to_get_size() {
        return $this->_get_arr_size ( '3' );
    }
    function filter() {
        return $this->_get_value ( '4' );
    }
    function set_filter($value) {
        return $this->_set_value ( '4', $value );
    }
}
class BatchGetRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'TableInBatchGetRowRequest';
        $this->values['1'] = array();
    }
    function tables($offset) {
        return $this->_get_arr_value ( '1', $offset );
    }
    function add_tables() {
        return $this->_add_arr_value ( '1' );
    }
    function set_tables($index, $value) {
        $this->_set_arr_value ( '1', $index, $value );
    }
    function remove_last_tables() {
        $this->_remove_last_arr_value ( '1' );
    }
    function tables_size() {
        return $this->_get_arr_size ( '1' );
    }
}
class RowInBatchGetRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBBool';
        $this->values['1'] = '';
        $this->fields['2'] = 'Error';
        $this->values['2'] = '';
        $this->fields['3'] = 'ConsumedCapacity';
        $this->values['3'] = '';
        $this->fields['4'] = 'Row';
        $this->values['4'] = '';
    }
    function is_ok() {
        return $this->_get_value ( '1' );
    }
    function set_is_ok($value) {
        return $this->_set_value ( '1', $value );
    }
    function error() {
        return $this->_get_value ( '2' );
    }
    function set_error($value) {
        return $this->_set_value ( '2', $value );
    }
    function consumed() {
        return $this->_get_value ( '3' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '3', $value );
    }
    function row() {
        return $this->_get_value ( '4' );
    }
    function set_row($value) {
        return $this->_set_value ( '4', $value );
    }
}
class TableInBatchGetRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'RowInBatchGetRowResponse';
        $this->values['2'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function rows($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_rows() {
        return $this->_add_arr_value ( '2' );
    }
    function set_rows($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_rows() {
        $this->_remove_last_arr_value ( '2' );
    }
    function rows_size() {
        return $this->_get_arr_size ( '2' );
    }
}
class BatchGetRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'TableInBatchGetRowResponse';
        $this->values['1'] = array();
    }
    function tables($offset) {
        return $this->_get_arr_value ( '1', $offset );
    }
    function add_tables() {
        return $this->_add_arr_value ( '1' );
    }
    function set_tables($index, $value) {
        $this->_set_arr_value ( '1', $index, $value );
    }
    function remove_last_tables() {
        $this->_remove_last_arr_value ( '1' );
    }
    function tables_size() {
        return $this->_get_arr_size ( '1' );
    }
}
class PutRowInBatchWriteRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'Condition';
        $this->values['1'] = '';
        $this->fields['2'] = 'Column';
        $this->values['2'] = array();
        $this->fields['3'] = 'Column';
        $this->values['3'] = array();
    }
    function condition() {
        return $this->_get_value ( '1' );
    }
    function set_condition($value) {
        return $this->_set_value ( '1', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '2' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '2' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '2' );
    }
    function attribute_columns($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_attribute_columns() {
        return $this->_add_arr_value ( '3' );
    }
    function set_attribute_columns($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_attribute_columns() {
        $this->_remove_last_arr_value ( '3' );
    }
    function attribute_columns_size() {
        return $this->_get_arr_size ( '3' );
    }
}
class UpdateRowInBatchWriteRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'Condition';
        $this->values['1'] = '';
        $this->fields['2'] = 'Column';
        $this->values['2'] = array();
        $this->fields['3'] = 'ColumnUpdate';
        $this->values['3'] = array();
    }
    function condition() {
        return $this->_get_value ( '1' );
    }
    function set_condition($value) {
        return $this->_set_value ( '1', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '2' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '2' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '2' );
    }
    function attribute_columns($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_attribute_columns() {
        return $this->_add_arr_value ( '3' );
    }
    function set_attribute_columns($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_attribute_columns() {
        $this->_remove_last_arr_value ( '3' );
    }
    function attribute_columns_size() {
        return $this->_get_arr_size ( '3' );
    }
}
class DeleteRowInBatchWriteRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'Condition';
        $this->values['1'] = '';
        $this->fields['2'] = 'Column';
        $this->values['2'] = array();
    }
    function condition() {
        return $this->_get_value ( '1' );
    }
    function set_condition($value) {
        return $this->_set_value ( '1', $value );
    }
    function primary_key($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_primary_key() {
        return $this->_add_arr_value ( '2' );
    }
    function set_primary_key($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_primary_key() {
        $this->_remove_last_arr_value ( '2' );
    }
    function primary_key_size() {
        return $this->_get_arr_size ( '2' );
    }
}
class TableInBatchWriteRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'PutRowInBatchWriteRowRequest';
        $this->values['2'] = array();
        $this->fields['3'] = 'UpdateRowInBatchWriteRowRequest';
        $this->values['3'] = array();
        $this->fields['4'] = 'DeleteRowInBatchWriteRowRequest';
        $this->values['4'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function put_rows($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_put_rows() {
        return $this->_add_arr_value ( '2' );
    }
    function set_put_rows($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_put_rows() {
        $this->_remove_last_arr_value ( '2' );
    }
    function put_rows_size() {
        return $this->_get_arr_size ( '2' );
    }
    function update_rows($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_update_rows() {
        return $this->_add_arr_value ( '3' );
    }
    function set_update_rows($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_update_rows() {
        $this->_remove_last_arr_value ( '3' );
    }
    function update_rows_size() {
        return $this->_get_arr_size ( '3' );
    }
    function delete_rows($offset) {
        return $this->_get_arr_value ( '4', $offset );
    }
    function add_delete_rows() {
        return $this->_add_arr_value ( '4' );
    }
    function set_delete_rows($index, $value) {
        $this->_set_arr_value ( '4', $index, $value );
    }
    function remove_last_delete_rows() {
        $this->_remove_last_arr_value ( '4' );
    }
    function delete_rows_size() {
        return $this->_get_arr_size ( '4' );
    }
}
class BatchWriteRowRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'TableInBatchWriteRowRequest';
        $this->values['1'] = array();
    }
    function tables($offset) {
        return $this->_get_arr_value ( '1', $offset );
    }
    function add_tables() {
        return $this->_add_arr_value ( '1' );
    }
    function set_tables($index, $value) {
        $this->_set_arr_value ( '1', $index, $value );
    }
    function remove_last_tables() {
        $this->_remove_last_arr_value ( '1' );
    }
    function tables_size() {
        return $this->_get_arr_size ( '1' );
    }
}
class RowInBatchWriteRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBBool';
        $this->values['1'] = '';
        $this->fields['2'] = 'Error';
        $this->values['2'] = '';
        $this->fields['3'] = 'ConsumedCapacity';
        $this->values['3'] = '';
    }
    function is_ok() {
        return $this->_get_value ( '1' );
    }
    function set_is_ok($value) {
        return $this->_set_value ( '1', $value );
    }
    function error() {
        return $this->_get_value ( '2' );
    }
    function set_error($value) {
        return $this->_set_value ( '2', $value );
    }
    function consumed() {
        return $this->_get_value ( '3' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '3', $value );
    }
}
class TableInBatchWriteRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'RowInBatchWriteRowResponse';
        $this->values['2'] = array();
        $this->fields['3'] = 'RowInBatchWriteRowResponse';
        $this->values['3'] = array();
        $this->fields['4'] = 'RowInBatchWriteRowResponse';
        $this->values['4'] = array();
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function put_rows($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_put_rows() {
        return $this->_add_arr_value ( '2' );
    }
    function set_put_rows($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_put_rows() {
        $this->_remove_last_arr_value ( '2' );
    }
    function put_rows_size() {
        return $this->_get_arr_size ( '2' );
    }
    function update_rows($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_update_rows() {
        return $this->_add_arr_value ( '3' );
    }
    function set_update_rows($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_update_rows() {
        $this->_remove_last_arr_value ( '3' );
    }
    function update_rows_size() {
        return $this->_get_arr_size ( '3' );
    }
    function delete_rows($offset) {
        return $this->_get_arr_value ( '4', $offset );
    }
    function add_delete_rows() {
        return $this->_add_arr_value ( '4' );
    }
    function set_delete_rows($index, $value) {
        $this->_set_arr_value ( '4', $index, $value );
    }
    function remove_last_delete_rows() {
        $this->_remove_last_arr_value ( '4' );
    }
    function delete_rows_size() {
        return $this->_get_arr_size ( '4' );
    }
}
class BatchWriteRowResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'TableInBatchWriteRowResponse';
        $this->values['1'] = array();
    }
    function tables($offset) {
        return $this->_get_arr_value ( '1', $offset );
    }
    function add_tables() {
        return $this->_add_arr_value ( '1' );
    }
    function set_tables($index, $value) {
        $this->_set_arr_value ( '1', $index, $value );
    }
    function remove_last_tables() {
        $this->_remove_last_arr_value ( '1' );
    }
    function tables_size() {
        return $this->_get_arr_size ( '1' );
    }
}
class Direction extends PBEnum {
    const FORWARD = 0;
    const BACKWARD = 1;
}
class GetRangeRequest extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'PBString';
        $this->values['1'] = '';
        $this->fields['2'] = 'Direction';
        $this->values['2'] = '';
        $this->fields['3'] = 'PBString';
        $this->values['3'] = array();
        $this->fields['4'] = 'PBInt';
        $this->values['4'] = '';
        $this->fields['5'] = 'Column';
        $this->values['5'] = array();
        $this->fields['6'] = 'Column';
        $this->values['6'] = array();
        $this->fields['7'] = 'ColumnCondition';
        $this->values['7'] = '';
    }
    function table_name() {
        return $this->_get_value ( '1' );
    }
    function set_table_name($value) {
        return $this->_set_value ( '1', $value );
    }
    function direction() {
        return $this->_get_value ( '2' );
    }
    function set_direction($value) {
        return $this->_set_value ( '2', $value );
    }
    function columns_to_get($offset) {
        $v = $this->_get_arr_value ( '3', $offset );
        return $v->get_value ();
    }
    function append_columns_to_get($value) {
        $v = $this->_add_arr_value ( '3' );
        $v->set_value ( $value );
    }
    function set_columns_to_get($index, $value) {
        $v = new $this->fields['3'] ();
        $v->set_value ( $value );
        $this->_set_arr_value ( '3', $index, $v );
    }
    function remove_last_columns_to_get() {
        $this->_remove_last_arr_value ( '3' );
    }
    function columns_to_get_size() {
        return $this->_get_arr_size ( '3' );
    }
    function limit() {
        return $this->_get_value ( '4' );
    }
    function set_limit($value) {
        return $this->_set_value ( '4', $value );
    }
    function inclusive_start_primary_key($offset) {
        return $this->_get_arr_value ( '5', $offset );
    }
    function add_inclusive_start_primary_key() {
        return $this->_add_arr_value ( '5' );
    }
    function set_inclusive_start_primary_key($index, $value) {
        $this->_set_arr_value ( '5', $index, $value );
    }
    function remove_last_inclusive_start_primary_key() {
        $this->_remove_last_arr_value ( '5' );
    }
    function inclusive_start_primary_key_size() {
        return $this->_get_arr_size ( '5' );
    }
    function exclusive_end_primary_key($offset) {
        return $this->_get_arr_value ( '6', $offset );
    }
    function add_exclusive_end_primary_key() {
        return $this->_add_arr_value ( '6' );
    }
    function set_exclusive_end_primary_key($index, $value) {
        $this->_set_arr_value ( '6', $index, $value );
    }
    function remove_last_exclusive_end_primary_key() {
        $this->_remove_last_arr_value ( '6' );
    }
    function exclusive_end_primary_key_size() {
        return $this->_get_arr_size ( '6' );
    }
    function filter() {
        return $this->_get_value ( '7' );
    }
    function set_filter($value) {
        return $this->_set_value ( '7', $value );
    }
}
class GetRangeResponse extends PBMessage {
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader = null) {
        parent::__construct ( $reader );
        $this->fields['1'] = 'ConsumedCapacity';
        $this->values['1'] = '';
        $this->fields['2'] = 'Column';
        $this->values['2'] = array();
        $this->fields['3'] = 'Row';
        $this->values['3'] = array();
    }
    function consumed() {
        return $this->_get_value ( '1' );
    }
    function set_consumed($value) {
        return $this->_set_value ( '1', $value );
    }
    function next_start_primary_key($offset) {
        return $this->_get_arr_value ( '2', $offset );
    }
    function add_next_start_primary_key() {
        return $this->_add_arr_value ( '2' );
    }
    function set_next_start_primary_key($index, $value) {
        $this->_set_arr_value ( '2', $index, $value );
    }
    function remove_last_next_start_primary_key() {
        $this->_remove_last_arr_value ( '2' );
    }
    function next_start_primary_key_size() {
        return $this->_get_arr_size ( '2' );
    }
    function rows($offset) {
        return $this->_get_arr_value ( '3', $offset );
    }
    function add_rows() {
        return $this->_add_arr_value ( '3' );
    }
    function set_rows($index, $value) {
        $this->_set_arr_value ( '3', $index, $value );
    }
    function remove_last_rows() {
        $this->_remove_last_arr_value ( '3' );
    }
    function rows_size() {
        return $this->_get_arr_size ( '3' );
    }
}
?>