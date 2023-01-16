<?php


namespace Aliyun\OTS\PlainBuffer;


use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\UpdateTypeConst;
use Aliyun\OTS\ProtoBuffer\Protocol\PrimaryKeyType;

class PlainBufferCodedOutputStream
{
    var $output = null;

    public function __construct(PlainBufferOutputStream $output)
    {
        $this->output = $output;
    }

    /*
     * cell_name = tag_cell_name formated_value
     * formated_value = value_len value_data
     */
    private function writeCellName($name, $cellCheckSum)
    {
        self::writeTag(PlainBufferConsts::TAG_CELL_NAME);

        // value_len
        $this->output->writeRawLittleEndian32(strlen($name));
        // value_data
        $this->output->writeBytes($name);
        $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $name);
        return $cellCheckSum;
    }

    /*
     * cell_value = tag_cell_value value_type formated_value
     * formated_value =  value_len value_data
     * value_type = int8
     * value_len = int32
     */
    private function writePrimaryKeyValue($pkValue, $cellCheckSum)
    {
        self::writeTag(PlainBufferConsts::TAG_CELL_VALUE);
        if ($pkValue['type'] == PrimaryKeyTypeConst::CONST_INF_MIN) {
            $this->output->writeRawLittleEndian32(1);
            $this->output->writeRawByte(PlainBufferConsts::VT_INF_MIN);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_INF_MIN);
        }
        else if ($pkValue['type'] == PrimaryKeyTypeConst::CONST_INF_MAX) {
            $this->output->writeRawLittleEndian32(1);
            $this->output->writeRawByte(PlainBufferConsts::VT_INF_MAX);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_INF_MAX);
        }
        else if ($pkValue['type'] == PrimaryKeyTypeConst::CONST_PK_AUTO_INCR) {
            $this->output->writeRawLittleEndian32(1);
            $this->output->writeRawByte(PlainBufferConsts::VT_AUTO_INCREMENT);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_AUTO_INCREMENT);
        }
        else if ($pkValue['type'] == PrimaryKeyTypeConst::CONST_INTEGER) {
            $this->output->writeRawLittleEndian32(1 + PlainBufferConsts::LITTLE_ENDIAN_64_SIZE);
            $this->output->writeRawByte(PlainBufferConsts::VT_INTEGER);
            $this->output->writeRawLittleEndian64($pkValue['value']);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_INTEGER);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $pkValue['value']);
        }
        else if ($pkValue['type'] == PrimaryKeyTypeConst::CONST_STRING) {
            $stringValue = $pkValue['value'];
            $prefixLength = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE + 1;
            $this->output->writeRawLittleEndian32($prefixLength + strlen($stringValue));
            $this->output->writeRawByte(PlainBufferConsts::VT_STRING);
            $this->output->writeRawLittleEndian32(strlen($stringValue));
            $this->output->writeBytes($stringValue);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_STRING);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, strlen($stringValue));
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $stringValue);
        }
        else if ($pkValue['type'] == PrimaryKeyTypeConst::CONST_BINARY) {
            $stringValue = $pkValue['value'];
            $prefixLength = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE + 1;
            $this->output->writeRawLittleEndian32($prefixLength + strlen($stringValue));
            $this->output->writeRawByte(PlainBufferConsts::VT_BLOB);
            $this->output->writeRawLittleEndian32(strlen($stringValue));
            $this->output->writeBytes($stringValue);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_BLOB);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, strlen($stringValue));
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $stringValue);
        }
        else {
            throw new \Aliyun\OTS\OTSClientException("Unsupported primary key type:" . gettype($value));
        }
        return $cellCheckSum;
    }

    /*
     * cell_value = tag_cell_value value_type formated_value
     * formated_value =  value_len value_data
     * value_type = int8
     * value_len = int32
     */
    private function writeColumnValueWithChecksum($columnValue, $cellCheckSum)
    {
        self::writeTag(PlainBufferConsts::TAG_CELL_VALUE);
        $value = $columnValue['value'];
        if ($columnValue['type'] == ColumnTypeConst::CONST_BOOLEAN) {
            $this->output->writeRawLittleEndian32(2);
            $this->output->writeRawByte(PlainBufferConsts::VT_BOOLEAN);
            $this->output->writeBoolean($value);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_BOOLEAN);
            if ($value) {
                $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, 1);
            } else {
                $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, 0);
            }
        } else if ($columnValue['type'] == ColumnTypeConst::CONST_INTEGER) {
            $this->output->writeRawLittleEndian32(1 + PlainBufferConsts::LITTLE_ENDIAN_64_SIZE);
            $this->output->writeRawByte(PlainBufferConsts::VT_INTEGER);
            $this->output->writeRawLittleEndian64($value);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_INTEGER);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $value);
        } else if ($columnValue['type'] == ColumnTypeConst::CONST_STRING) {
            $prefixLength = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE + 1;
            $this->output->writeRawLittleEndian32($prefixLength + strlen($value));
            $this->output->writeRawByte(PlainBufferConsts::VT_STRING);
            $this->output->writeRawLittleEndian32(strlen($value));
            $this->output->writeBytes($value);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_STRING);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, strlen($value));
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $value);
        }
        else if ($columnValue['type'] == ColumnTypeConst::CONST_BINARY) {
            $prefixLength = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE + 1;
            $this->output->writeRawLittleEndian32($prefixLength + strlen($value));
            $this->output->writeRawByte(PlainBufferConsts::VT_BLOB);
            $this->output->writeRawLittleEndian32(strlen($value));
            $this->output->writeBytes($value);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_BLOB);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, strlen($value));
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $value);
        }
        else if ($columnValue['type'] == ColumnTypeConst::CONST_DOUBLE) {
            $this->output->writeRawLittleEndian32(1 + PlainBufferConsts::LITTLE_ENDIAN_64_SIZE);
            $this->output->writeRawByte(PlainBufferConsts::VT_DOUBLE);
            $this->output->writeDouble($value);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_DOUBLE);
            $cellCheckSum = PlainBufferCrc8::crcDouble($cellCheckSum, $value);
        } else {
            throw new \Aliyun\OTS\OTSClientException("Unsupported column type:" . gettype($value));
        }
        return $cellCheckSum;
    }

    public function writeColumnValue($columnValue)
    {
        $value = $columnValue['value'];

        if ($columnValue['type'] == ColumnTypeConst::CONST_BOOLEAN) {
            $this->output->writeRawByte(PlainBufferConsts::VT_BOOLEAN);
            $this->output->writeBoolean($value);
        }
        else if ($columnValue['type'] == ColumnTypeConst::CONST_INTEGER) {
            $this->output->writeRawByte(PlainBufferConsts::VT_INTEGER);
            $this->output->writeRawLittleEndian64($value);
        }
        else if ($columnValue['type'] == ColumnTypeConst::CONST_STRING) {
            $this->output->writeRawByte(PlainBufferConsts::VT_STRING);
            $this->output->writeRawLittleEndian32(strlen($value));
            $this->output->writeBytes($value);
        }
        else if ($columnValue['type'] == ColumnTypeConst::CONST_BINARY) {
            $this->output->writeRawByte(PlainBufferConsts::VT_BLOB);
            $this->output->writeRawLittleEndian32(strlen($value));
            $this->output->writeBytes($value);
        }
        else if ($columnValue['type'] == ColumnTypeConst::CONST_DOUBLE) {
            $this->output->writeRawByte(PlainBufferConsts::VT_DOUBLE);
            $this->output->writeDouble($value);
        } else {
            throw new \Aliyun\OTS\OTSClientException("Unsupported column type:" . gettype($value));
        }
    }

    /*
     * cell = tag_cell cell_name [cell_value] [cell_op(omit)] [cell_ts] cell_checksum
     */
    private function writeColumn($columnName, $columnValue, $timestamp, $rowChecksum)
    {
        self::writeTag(PlainBufferConsts::TAG_CELL);

        $cellCheckSum = 0;
        // cell_name
        $cellCheckSum = self::writeCellName($columnName, $cellCheckSum);
        // cell_value
        $cellCheckSum = self::writeColumnValueWithChecksum($columnValue, $cellCheckSum);

        if ($timestamp != null) {
            // cell_ts
            self::writeTag(PlainBufferConsts::TAG_CELL_TIMESTAMP);
            $this->output->writeRawLittleEndian64($timestamp);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $timestamp);
        }
        // cell_checksum
        self::writeTag(PlainBufferConsts::TAG_CELL_CHECKSUM);
        $this->output->writeRawByte($cellCheckSum);

        $rowChecksum = PlainBufferCrc8::crcInt8($rowChecksum, $cellCheckSum);
        return $rowChecksum;
    }

    /*
     * cell = tag_cell cell_name [cell_value] [cell_op] [cell_ts] cell_checksum
     */
    private function writeUpdateColumn($updateType, $columnName, $columnValue, $rowChecksum)
    {
        self::writeTag(PlainBufferConsts::TAG_CELL);
        $timestamp = null;

        $cellCheckSum = 0;
        // cell_name
        $cellCheckSum = self::writeCellName($columnName, $cellCheckSum);
        // cell_value
        if($columnValue != null) {
            if(is_array($columnValue)) {
                if(!is_null($columnValue['value'])) {
                    $cellCheckSum = self::writeColumnValueWithChecksum($columnValue, $cellCheckSum);
                }
                if($columnValue['timestamp'] != null) {
                    $timestamp = $columnValue['timestamp'];
                }
            } else if(is_string($columnValue)) {
                $cellCheckSum = self::writeColumnValueWithChecksum($columnValue, $cellCheckSum);
            }
        }

        // cell_op
        if($updateType == UpdateTypeConst::CONST_DELETE) {
            self::writeTag(PlainBufferConsts::TAG_CELL_TYPE);
            $this->output->writeRawByte(PlainBufferConsts::DELETE_ONE_VERSION);
        } else if($updateType == UpdateTypeConst::CONST_DELETE_ALL) {
            self::writeTag(PlainBufferConsts::TAG_CELL_TYPE);
            $this->output->writeRawByte(PlainBufferConsts::DELETE_ALL_VERSION);
        } else if($updateType == UpdateTypeConst::CONST_INCREMENT) {
            self::writeTag(PlainBufferConsts::TAG_CELL_TYPE);
            $this->output->writeRawByte(PlainBufferConsts::INCREMENT);
        }

        // cell_ts
        if ($timestamp != null) {
            self::writeTag(PlainBufferConsts::TAG_CELL_TIMESTAMP);
            $this->output->writeRawLittleEndian64($timestamp);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $timestamp);
        }

        // NOTE：这里特别注意下计算crc的顺序， cell_op/cell_type 在cell_timestamp之后，虽然数据是在前面
        if($updateType == UpdateTypeConst::CONST_DELETE) {
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::DELETE_ONE_VERSION);
        } else if($updateType == UpdateTypeConst::CONST_DELETE_ALL) {
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::DELETE_ALL_VERSION);
        } else if($updateType == UpdateTypeConst::CONST_INCREMENT) {
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::INCREMENT);
        }

        // cell_checksum
        self::writeTag(PlainBufferConsts::TAG_CELL_CHECKSUM);
        $this->output->writeRawByte($cellCheckSum);

        $rowChecksum = PlainBufferCrc8::crcInt8($rowChecksum, $cellCheckSum);
        return $rowChecksum;
    }

    /*
     * cell = tag_cell cell_name [cell_value] [cell_op(omit)] [cell_ts(omit)] cell_checksum
     */
    private function writePrimaryKeyColumn($pkName, $pkValue, $rowChecksum)
    {
        self::writeTag(PlainBufferConsts::TAG_CELL);

        $cellCheckSum = 0;
        // cell_name
        $cellCheckSum = self::writeCellName($pkName, $cellCheckSum);
        // cell_value
        $cellCheckSum = self::writePrimaryKeyValue($pkValue, $cellCheckSum);

        //cell_checksum
        self::writeTag(PlainBufferConsts::TAG_CELL_CHECKSUM);
        $this->output->writeRawByte($cellCheckSum);

        $rowChecksum = PlainBufferCrc8::crcInt8($rowChecksum, $cellCheckSum);
        return $rowChecksum;
    }

    public function writeHeader()
    {
        $this->output->writeRawLittleEndian32(PlainBufferConsts::HEADER);
    }

    public function writeTag($tag)
    {
        $this->output->writeRawByte($tag);
    }

    public function writeRowChecksum($rowChecksum)
    {
        self::writeTag(PlainBufferConsts::TAG_ROW_CHECKSUM);
        $this->output->writeRawByte($rowChecksum);
    }

    /*
     * pk = tag_pk cell_1 [cell_2] [cell_3]
     */
    public function writePrimaryKey($primaryKey, $rowChecksum)
    {
        self::writeTag(PlainBufferConsts::TAG_ROW_PK);
        foreach ($primaryKey as $pk) {
            // cell
            $rowChecksum = self::writePrimaryKeyColumn($pk["name"], $pk["value"], $rowChecksum);
        }
        return $rowChecksum;
    }

    /*
     * attr  = tag_attr cell1 [cell_2] [cell_3]
     */
    public function writeColumns($attributeColumns, $rowChecksum)
    {
        if ($attributeColumns != null and count($attributeColumns) != 0) {
            self::writeTag(PlainBufferConsts::TAG_ROW_DATA);
            foreach ($attributeColumns as $attr) {
                // cell
                $rowChecksum = self::writeColumn($attr["name"], $attr["value"], $attr['value']['timestamp'], $rowChecksum);
            }
        }
        return $rowChecksum;
    }

    /*
     * attr  = tag_attr cell1 [cell_2] [cell_3]
    */
    public function writeUpdateColumns($attributeColumns, $rowChecksum)
    {
        if(count($attributeColumns) != 0) {
            self::writeTag(PlainBufferConsts::TAG_ROW_DATA);
            foreach ($attributeColumns as $updateType => $columns) {
                foreach($columns as $column) {
                    if(is_string($column)) {
                        $rowChecksum = self::writeUpdateColumn($updateType, $column, null, $rowChecksum);
                    }else if(is_array($column) && $column['value'] != null) {
                        $rowChecksum = self::writeUpdateColumn($updateType, $column['name'], $column['value'], $rowChecksum);
                    }
                    else {
                        throw new \Aliyun\OTS\OTSClientException("Unsupported column type:" . gettype($column));
                    }
                }
            }
        }
        return $rowChecksum;
    }


    public function writeDeleteMarker($rowChecksum)
    {
        self::writeTag(PlainBufferConsts::TAG_DELETE_ROW_MARKER);
        return PlainBufferCrc8::crcInt8($rowChecksum, 1);
    }

    public function writeSearchValue($searchValue)
    {
        $value = $searchValue["value"];
        if ($searchValue['type'] == ColumnTypeConst::CONST_BOOLEAN) {
            $this->output->writeRawByte(PlainBufferConsts::VT_BOOLEAN);
            $this->output->writeBoolean($value);
        }
        else if ($searchValue['type'] == ColumnTypeConst::CONST_INTEGER) {
            $this->output->writeRawByte(PlainBufferConsts::VT_INTEGER);
            $this->output->writeRawLittleEndian64($value);
        }
        else if ($searchValue['type'] == ColumnTypeConst::CONST_STRING) {
            $this->output->writeRawByte(PlainBufferConsts::VT_STRING);
            $this->output->writeRawLittleEndian32(strlen($value));
            $this->output->writeBytes($value);
        }
        else if ($searchValue['type'] == ColumnTypeConst::CONST_DOUBLE) {
            $this->output->writeRawByte(PlainBufferConsts::VT_DOUBLE);
            $this->output->writeDouble($value);
        } else {
            throw new \Aliyun\OTS\OTSClientException("Unsupported searchValue type:" . gettype($value));
        }
    }

    // just for test
    public function toString()
    {
        $ret = array();
        for ($i = 0; $i < strlen($this->output->buffer); $i++) {
            $ret[] = (PlainBufferCrc8::toByte(ord($this->output->buffer[$i])));
        }
        return $ret;
    }
}

