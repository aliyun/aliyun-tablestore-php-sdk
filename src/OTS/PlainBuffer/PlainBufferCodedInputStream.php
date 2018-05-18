<?php

namespace Aliyun\OTS\PlainBuffer;


use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\OTSClientException;

class PlainBufferCodedInputStream
{
    var $input = null;

    public function __construct(PlainBufferInputStream $input)
    {
        $this->input = $input;
    }

    private function readPrimaryKeyValue($cellCheckSum)
    {
        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL_VALUE)) {
            throw new OTSClientException("Expect TAG_CELL_VALUE but it was " . self::getLastTag());
        }

        $this->input->readRawLittleEndian32();
        $columnType = ord($this->input->readRawByte());
        if ($columnType == PlainBufferConsts::VT_INTEGER) {
            $int64Value = $this->input->readInt64();
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_INTEGER);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $int64Value);
            self::readTag();
            return array("value" => $int64Value, "cell_check_sum" => $cellCheckSum, "type" => PrimaryKeyTypeConst::CONST_INTEGER);
        }
        else if ($columnType == PlainBufferConsts::VT_STRING) {
            $valueSize = $this->input->readInt32();
            $stringValue = $this->input->readUtfString($valueSize);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_STRING);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, $valueSize);
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $stringValue);
            self::readTag();
            return array("value" => $stringValue, "cell_check_sum" => $cellCheckSum, "type" => PrimaryKeyTypeConst::CONST_STRING);
        }
        else if ($columnType == PlainBufferConsts::VT_BLOB) {
            $valueSize = $this->input->readInt32();
            $binaryValue = $this->input->readBytes($valueSize);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_BLOB);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, $valueSize);
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $binaryValue);
            self::readTag();
            return array("value" => $binaryValue, "cell_check_sum" => $cellCheckSum, "type" => PrimaryKeyTypeConst::CONST_BINARY);
        }
        else {
            throw new OTSClientException("Unsupported primary key type: " . $columnType);
        }
    }
    
    private function readColumnValue($cellCheckSum)
    {
        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL_VALUE)) {
            throw new OTSClientException("Expect TAG_CELL_VALUE but it was " . self::getLastTag());
        }
        $this->input->readRawLittleEndian32();
        $columnType = ord($this->input->readRawByte());
        if ($columnType == PlainBufferConsts::VT_INTEGER) {
            $int64Value = $this->input->readInt64();
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_INTEGER);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $int64Value);
            self::readTag();
            return array("value" => $int64Value, "cell_check_sum" => $cellCheckSum, 'type'=> ColumnTypeConst::CONST_INTEGER);
        }
        else if ($columnType == PlainBufferConsts::VT_STRING) {
            $valueSize = $this->input->readInt32();
            $stringValue = $this->input->readUtfString($valueSize);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_STRING);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, $valueSize);
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $stringValue);
            self::readTag();
            return array("value" => $stringValue, "cell_check_sum" => $cellCheckSum, 'type' => ColumnTypeConst::CONST_STRING);
        }
        else if ($columnType == PlainBufferConsts::VT_BLOB) {
            $valueSize = $this->input->readInt32();
            $binaryValue = $this->input->readBytes($valueSize);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_BLOB);
            $cellCheckSum = PlainBufferCrc8::crcInt32($cellCheckSum, $valueSize);
            $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $binaryValue);
            self::readTag();
            return array("value" => $binaryValue, "cell_check_sum" => $cellCheckSum, 'type' => ColumnTypeConst::CONST_BINARY);
        }
        else if ($columnType == PlainBufferConsts::VT_BOOLEAN) {
            $boolValue = $this->input->readBoolean();
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_BOOLEAN);
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, $boolValue);
            self::readTag();
            return array("value" => $boolValue, "cell_check_sum" => $cellCheckSum, 'type' => ColumnTypeConst::CONST_BOOLEAN);
        }
        else if ($columnType == PlainBufferConsts::VT_DOUBLE) {
            $doubleInt = $this->input->readDouble();
            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, PlainBufferConsts::VT_DOUBLE);
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $doubleInt);
            self::readTag();
            $doubleValue = unpack('d', pack('P', $doubleInt))[1];
            return array("value" => $doubleValue, "cell_check_sum" => $cellCheckSum, 'type' => ColumnTypeConst::CONST_DOUBLE);
        }
        else {
            throw new OTSClientException("Unsupported column type: " . $columnType);
        }
    }

    private function readColumn($rowCheckSum)
    {
        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL)) {
            throw new OTSClientException("Expect TAG_CELL but it was " . self::getLastTag());
        }
        self::readTag();

        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL_NAME)) {
            throw new OTSClientException("Expect TAG_CELL_NAME but it was " . self::getLastTag());
        }

        $cellCheckSum = 0;
        $columnName = null;
        $columnValue = null;
        $columnType = null;
        $timestamp = null;
        $nameSize = $this->input->readRawLittleEndian32();
        $columnName = $this->input->readUtfString($nameSize);
        $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $columnName);
        self::readTag();

        if (self::getLastTag() == PlainBufferConsts::TAG_CELL_VALUE) {
            $column = self::readColumnValue($cellCheckSum);
            $columnValue = $column['value'];
            $columnType = $column['type'];
            $cellCheckSum = $column['cell_check_sum'];
        }
        // skip CELL_TYPE
//        if (self::getLastTag() == PlainBufferConsts::TAG_CELL_TYPE) {
//            $cellCheckSum = PlainBufferCrc8::crcInt8($cellCheckSum, $cellType);
//            self::readTag();
//        }
        if (self::getLastTag() == PlainBufferConsts::TAG_CELL_TIMESTAMP) {
            $timestamp = $this->input->readInt64();
            $cellCheckSum = PlainBufferCrc8::crcInt64($cellCheckSum, $timestamp);
            self::readTag();
        }
        if (self::getLastTag() == PlainBufferConsts::TAG_CELL_CHECKSUM) {
            $checkSum = PlainBufferCrc8::toByte(ord($this->input->readRawByte()));
            if ($checkSum != $cellCheckSum) {
                throw new OTSClientException("Checksum mismatch. expected:" . $checkSum. ",actual:" . $cellCheckSum);
            }
            self::readTag();
        }
        else {
            throw new OTSClientException("Expect TAG_CELL_CHECKSUM but it was " . self::getLastTag());
        }

        $rowCheckSum = PlainBufferCrc8::crcInt8($rowCheckSum, $cellCheckSum);
        return array(
          "column_name" => $columnName,
          "column_value" => $columnValue,
          "column_type" => $columnType,
          "timestamp" => $timestamp,
          "row_check_sum"=> $rowCheckSum
        );
    }

    private function readPrimaryKeyColumn($rowCheckSum)
    {
        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL)) {
            throw new OTSClientException("Expect TAG_CELL but it was " . self::getLastTag());
        }
        self::readTag();

        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL_NAME)) {
            throw new OTSClientException("Expect TAG_CELL_NAME but it was " . self::getLastTag());
        }

        $cellCheckSum = 0;
        $nameSize = $this->input->readRawLittleEndian32();
        $columnName = $this->input->readUtfString($nameSize);
        $cellCheckSum = PlainBufferCrc8::crcString($cellCheckSum, $columnName);
        self::readTag();

        if (!self::checkLastTagWas(PlainBufferConsts::TAG_CELL_VALUE)) {
            throw new OTSClientException("Expect TAG_CELL_VALUE but it was " . self::getLastTag());
        }
        $primaryKey = self::readPrimaryKeyValue($cellCheckSum);

        $primaryKeyValue = $primaryKey['value'];
        $primaryKeyType = $primaryKey['type'];
        $cellCheckSum = $primaryKey['cell_check_sum'];

        if (self::getLastTag() == PlainBufferConsts::TAG_CELL_CHECKSUM) {
            $checkSum = PlainBufferCrc8::toByte(ord($this->input->readRawByte()));
            if ($checkSum != $cellCheckSum) {
                throw new OTSClientException("Checksum mismatch. expected:" . $checkSum. ",actual:" . $cellCheckSum);
            }
            self::readTag();
        }
        else {
            throw new OTSClientException("Expect TAG_CELL_CHECKSUM but it was " . self::getLastTag());
        }

        $rowCheckSum = PlainBufferCrc8::crcInt8($rowCheckSum, $cellCheckSum);
        return array(
            "column_name" => $columnName,
            "primary_key_value" => $primaryKeyValue,
            "primary_key_type" => $primaryKeyType,
            "row_check_sum" => $rowCheckSum
        );
    }

    private function getLastTag()
    {
        return $this->input->getLastTag();
    }

    private function checkLastTagWas($tag)
    {
        return $this->input->checkLastTagWas($tag);
    }

    public function readRow()
    {
        if(self::readHeader() != PlainBufferConsts::HEADER) {
            throw new OTSClientException("Invalid header from plain buffer.");
        }
        self::readTag();
        return self::readRowWithoutHeader();
    }

    public function readRows()
    {
        if(self::readHeader() != PlainBufferConsts::HEADER) {
            throw new OTSClientException("Invalid header from plain buffer.");
        }
        self::readTag();
        $rowList = array();
        while (!$this->input->isAtEnd()) {
            $rowList[] = self::readRowWithoutHeader();
        }
        return $rowList;
    }

    private function readHeader()
    {
        $ret = $this->input->readInt32();
        return $ret;
    }

    private function readTag()
    {
        return $this->input->readTag();
    }

    private function readRowWithoutHeader()
    {
        $rowCheckSum = 0;
        $ret = array();
        $primaryKey = [];
        $attributes = [];

        if (!self::checkLastTagWas(PlainBufferConsts::TAG_ROW_PK)) {
            throw new OTSClientException("Expect TAG_ROW_PK but it was " . self::getLastTag());
        }

        self::readTag();

        while (self::checkLastTagWas(PlainBufferConsts::TAG_CELL)) {
            $primaryKeyValue = self::readPrimaryKeyColumn($rowCheckSum);
            $name = $primaryKeyValue['column_name'];
            $value = $primaryKeyValue['primary_key_value'];
            $type = $primaryKeyValue['primary_key_type'];
            $rowCheckSum = $primaryKeyValue['row_check_sum'];
            if($type != PrimaryKeyTypeConst::CONST_BINARY) {
                $primaryKey[] = array($name, $value);
            }else {
                $primaryKey[] = array($name, $value, $type);
            }
        }
        if(self::checkLastTagWas(PlainBufferConsts::TAG_ROW_DATA)) {
            self::readTag();
            while (self::checkLastTagWas(PlainBufferConsts::TAG_CELL)) {
                $column = self::readColumn($rowCheckSum);
                $rowCheckSum = $column['row_check_sum'];
                $attributes[] = array($column['column_name'], $column['column_value'], $column['column_type'], $column['timestamp']);
            }
        }
        if (self::checkLastTagWas(PlainBufferConsts::TAG_DELETE_ROW_MARKER)) {
            self::readTag();
            $rowCheckSum = PlainBufferCrc8::crcInt8($rowCheckSum, 1);
        } else {
            $rowCheckSum = PlainBufferCrc8::crcInt8($rowCheckSum, 0);
        }

        if (self::checkLastTagWas(PlainBufferConsts::TAG_ROW_CHECKSUM)) {
            $checkSum = PlainBufferCrc8::toByte(ord($this->input->readRawByte()));
            if ($checkSum != $rowCheckSum) {
                throw new OTSClientException("Checksum is mismatch.");
            }
            self::readTag();
        } else {
            throw new OTSClientException("Expect TAG_ROW_CHECKSUM but it was " . self::getLastTag());
        }

        $ret['primary_key'] = $primaryKey;
        $ret['attribute_columns'] = $attributes;
        return $ret;
    }
}


