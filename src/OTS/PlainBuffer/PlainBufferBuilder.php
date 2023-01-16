<?php

namespace Aliyun\OTS\PlainBuffer;

// PlainBuffer格式定义：https://help.aliyun.com/document_detail/50600.html

use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\UpdateTypeConst;

class PlainBufferBuilder
{
    /*
     * plainbuffer = tag_header row1  [row2]  [row3]
     * row = ( pk [attr] | [pk] attr | pk attr ) [tag_delete_marker] row_checksum;
     */
    public static function serializeForPutRow($primaryKey, $attributeColumns) {
        $bufSize = PlainBufferBuilder::computePutRowSize($primaryKey, $attributeColumns);
        $outputStream = new PlainBufferOutputStream($bufSize);
        $codedOutputStream = new PlainBufferCodedOutputStream($outputStream);

        $rowChecksum = 0;
        // tag_header
        $codedOutputStream->writeHeader();
        // pk
        $rowChecksum = $codedOutputStream->writePrimaryKey($primaryKey, $rowChecksum);
        // attr
        $rowChecksum = $codedOutputStream->writeColumns($attributeColumns, $rowChecksum);
        $rowChecksum = PlainBufferCrc8::crcInt8($rowChecksum, 0);
        // row_checksum
        $codedOutputStream->writeRowChecksum($rowChecksum);
        return $outputStream->getBuffer();
    }

    public static function serializeForUpdateRow($primaryKey, $attributeColumns)
    {
        $bufSize = PlainBufferBuilder::computeUpdateRowSize($primaryKey, $attributeColumns);
        $outputStream = new PlainBufferOutputStream($bufSize);
        $codedOutputStream = new PlainBufferCodedOutputStream($outputStream);

        $rowChecksum = 0;
        // tag_header
        $codedOutputStream->writeHeader();
        // pk
        $rowChecksum = $codedOutputStream->writePrimaryKey($primaryKey, $rowChecksum);
        // attr
        $rowChecksum = $codedOutputStream->writeUpdateColumns($attributeColumns, $rowChecksum);
        $rowChecksum = PlainBufferCrc8::crcInt8($rowChecksum, 0);
        // row_checksum
        $codedOutputStream->writeRowChecksum($rowChecksum);
        return $outputStream->getBuffer();
    }

    public static function serializePrimaryKey($primaryKey)
    {
        $bufSize = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;
        $bufSize += PlainBufferBuilder::computePrimaryKeySize($primaryKey);
        $bufSize += 2;

        $outputStream = new PlainBufferOutputStream($bufSize);
        $codedOutputStream = new PlainBufferCodedOutputStream($outputStream);

        $rowChecksum = 0;
        $codedOutputStream->writeHeader();

        $rowChecksum = $codedOutputStream->writePrimaryKey($primaryKey, $rowChecksum);
        $rowChecksum = PlainBufferCrc8::crcInt8($rowChecksum, 0);
        $codedOutputStream->writeRowChecksum($rowChecksum);
        return $outputStream->getBuffer();
    }

    public static function serializeColumnValue($value)
    {
        $bufSize = PlainBufferBuilder::computeVariantValueSize($value);
        $outputStream = new PlainBufferOutputStream($bufSize);
        $codedOutputStream = new PlainBufferCodedOutputStream($outputStream);

        $codedOutputStream->writeColumnValue($value);
        return $outputStream->getBuffer();
    }

    private static function computeVariantValueSize($value)
    {
        return self::computeColumnValueSize($value) - PlainBufferConsts::LITTLE_ENDIAN_32_SIZE - 1;
    }


    public static function serializeForDeleteRow($primaryKey)
    {
        $bufSize = PlainBufferBuilder::computeDeleteRowSize($primaryKey);
        $outputStream = new PlainBufferOutputStream($bufSize);
        $codedOutputStream = new PlainBufferCodedOutputStream($outputStream);

        $rowChecksum = 0;
        $codedOutputStream->writeHeader();
        $rowChecksum = $codedOutputStream->writePrimaryKey($primaryKey, $rowChecksum);
        $rowChecksum = $codedOutputStream->writeDeleteMarker($rowChecksum);
        $codedOutputStream->writeRowChecksum($rowChecksum);
        return $outputStream->getBuffer();
    }

    public static function serializeSearchValue($value)
    {
        $bufSize = PlainBufferBuilder::computeVariantValueSize($value);
        $outputStream = new PlainBufferOutputStream($bufSize);
        $codedOutputStream = new PlainBufferCodedOutputStream($outputStream);

        $codedOutputStream->writeSearchValue($value);
        return $outputStream->getBuffer();
    }

    /*
     * cell_value = tag_cell_value value_type formated_value
     * formated_value =  value_len value_data
     * value_type = int8
     * value_len = int32
     */
    private static function computePrimaryKeyValueSize($value)
    {
        $size = 1;   // TAG_CELL_VALUE
        $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE + 1;  // length + type

        if(in_array($value['type'],['INF_MIN', 'INF_MAX', 'PK_AUTO_INCR'])){
            $size += 1;
            return $size;
        }
        if($value['type'] == PrimaryKeyTypeConst::CONST_INTEGER) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_64_SIZE; //sizeof(int64_t)
        } else if($value['type'] == PrimaryKeyTypeConst::CONST_STRING) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;
            $size += strlen($value['value']);
        } else if($value['type'] == PrimaryKeyTypeConst::CONST_BINARY) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;
            $size += strlen($value['value']);
        } else {
            throw new \Aliyun\OTS\OTSClientException("Unsupported primary key type:" . gettype($value['value']));
        }
        return $size;
    }

    private static function computeColumnValueSize($value)
    {
        $size = 1; // TAG_CELL_VALUE
        $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE + 1;  // length + type

        if($value['type'] == ColumnTypeConst::CONST_BOOLEAN) {
            $size += 1;
        }
        else if($value['type'] == ColumnTypeConst::CONST_INTEGER) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_64_SIZE;
        }
        if($value['type'] == ColumnTypeConst::CONST_STRING) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;
            $size += strlen($value['value']);
        }
        if($value['type'] == ColumnTypeConst::CONST_BINARY) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;
            $size += strlen($value['value']);
        }
        if($value['type'] == ColumnTypeConst::CONST_DOUBLE) {
            $size += PlainBufferConsts::LITTLE_ENDIAN_64_SIZE;
        }
        return $size;
    }

    private static function computeColumnSize($columnName, $columnValue, $timestamp = null)
    {
        $size = 1; // TAG_CELL

        $size += 1; // TAG_CELL_NAME
        $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE; // length
        $size += strlen($columnName);

        if (isset($columnValue['value'])) {
            $size += PlainBufferBuilder::computeColumnValueSize($columnValue);
        }
        if ($timestamp != null) {
            $size += 1 + PlainBufferConsts::LITTLE_ENDIAN_64_SIZE; // TAG_CELL_TIMESTAMP + timestamp
        }
        $size += 2; // TAG_CELL_CHECKSUM + checksum
        return $size;
    }

    private static function computeColumnSize2($columnName, $columnValue, $updateType)
    {
        $size = PlainBufferBuilder::computeColumnSize($columnName, $columnValue, $columnValue['timestamp']);
        if ($updateType == UpdateTypeConst::CONST_DELETE
            || $updateType == UpdateTypeConst::CONST_DELETE_ALL
            || $updateType == UpdateTypeConst::CONST_INCREMENT
        )
            $size += 2;
        return $size;
    }

    /*
    * cell = tag_cell cell_name [cell_value] [cell_op(omit)] [cell_ts(omit)] cell_checksum
    */
    private static function computePrimaryKeyColumnSize($pkName, $pkValue)
    {
        $size = 1; // TAG_CELL

        // cell_name
        $size += 1; // TAG_CELL_NAME
        $size += PlainBufferConsts::LITTLE_ENDIAN_32_SIZE; // length
        $size += strlen($pkName);
        // cell_value
        $size += PlainBufferBuilder::computePrimaryKeyValueSize($pkValue);
        // cell_checksum
        $size += 2;  // TAG_CELL_CHECKSUM + checksum
        return $size;
    }
    /*
     * pk = tag_pk cell_1 [cell_2] [cell_3]
     */
    private static function computePrimaryKeySize($primaryKey)
    {
        if (!is_array($primaryKey)){
            throw new \Aliyun\OTS\OTSClientException("Priamry key is not list, but is " . gettype($primaryKey));
        }
        $size = 1;  // TAG_ROW_PK
        foreach ($primaryKey as $pk) {
            // cell
            $size += PlainBufferBuilder::computePrimaryKeyColumnSize($pk["name"], $pk["value"]);
        }
        return $size;
    }
    /*
    * plainbuffer = tag_header row1  [row2]  [row3]
    * row = ( pk [attr] | [pk] attr | pk attr ) [tag_delete_marker] row_checksum;
     */
    private static function computePutRowSize($primaryKey, $attributeColumns)
    {
        $size = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;                   //HEADER
        // pk
        $size += PlainBufferBuilder::computePrimaryKeySize($primaryKey);

        // attr
        if (count($attributeColumns) != 0) {
            $size += 1;
            foreach ($attributeColumns as $attr) {
                $size += PlainBufferBuilder::computeColumnSize($attr["name"], $attr["value"], $attr['value']['timestamp']);
            }
        }
        $size += 2;
        return $size;
    }

    private static function computeUpdateRowSize($primaryKey, $attributeColumns)
    {
        $size = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;                   //HEADER
        // pk
        $size += PlainBufferBuilder::computePrimaryKeySize($primaryKey);
        // attr
        if (count($attributeColumns) != 0) {
            $size += 1;
            foreach ($attributeColumns as $updateType => $columns) {
                if(is_array($columns)) {
                    foreach($columns as $column) {
                        if(is_string($column)) {
                            $size += PlainBufferBuilder::computeColumnSize2($column, null, $updateType);
                        }
                        else if(count($column) == 1) {
                            $size += PlainBufferBuilder::computeColumnSize2($column['name'], null, $updateType);
                        }
                        else if(count($column) >= 2) {
                            $size += PlainBufferBuilder::computeColumnSize2($column['name'], $column['value'], $updateType);
                        }
                    }
                }
                else {
                    throw new \Aliyun\OTS\OTSClientException("Unsupported column type:" . gettype($columns));
                }
            }
        }
        // checksum
        $size += 2;
        return $size;
    }

    private static function computeDeleteRowSize($primaryKey)
    {
        $size = PlainBufferConsts::LITTLE_ENDIAN_32_SIZE;                   //HEADER
        $size += PlainBufferBuilder::computePrimaryKeySize($primaryKey);
        $size += 3;
        return $size;
    }
}