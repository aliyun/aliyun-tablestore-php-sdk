<?php

namespace Aliyun\OTS\Model;

use Aliyun\OTS\FlatBuffer\Protocol\DataType;
use Aliyun\OTS\FlatBuffer\Protocol\SQLResponseColumns;
use Aliyun\OTS\OTSClientException;

class SQLRows
{
    public $rowCount = 0;
    public $columnCount = 0;
    private $columnNames = array();
    private $columnTypes = array();
    private $columnTypeNames = array();
    private $columnValues = array();
    private $rleColumnValues = array();

    private $sqlTableMeta = null;

    public function __construct(SQLResponseColumns $columns)
    {
        $this->rowCount = $columns->getRowCount();
        $this->columnCount = $columns->getColumnsLength();
        $schemas = array();
        for ($columnIndex = 0; $columnIndex < $this->columnCount; $columnIndex++) {
            $tmpColumns = $columns->getColumns($columnIndex);
            $this->columnNames[$columnIndex] = $tmpColumns->getColumnName();
            $this->columnTypes[$columnIndex] = $tmpColumns->getColumnType();
            $this->columnTypeNames[$columnIndex] = DataType::Name($tmpColumns->getColumnType());
            $columnValue = $tmpColumns->getColumnValue();
            $this->columnValues[$columnIndex] = $columnValue;
            $this->rleColumnValues[$columnIndex] = $columnValue->getRleStringValues();

            $schemas[$columnIndex] = array(
                'index' => $columnIndex,
                'name' => $this->columnNames[$columnIndex],
                'type' => $this->columnTypes[$columnIndex],
                'type_name' => $this->columnTypeNames[$columnIndex]
            );
        }
        $this->sqlTableMeta = new SQLTableMeta($schemas);
    }

    /**
     * @throws OTSClientException
     */
    public function get($columnIndex, $rowIndex) {
        if ($columnIndex < 0 || $columnIndex >= $this->columnCount) {
            throw new OTSClientException('ColumnIndex ' . $columnIndex . ' is out of range.');
        }
        if ($rowIndex < 0 || $rowIndex >= $this->rowCount) {
            throw new OTSClientException('RowIndex ' . $rowIndex . ' is out of range.');
        }
        $columnValue = $this->columnValues[$columnIndex];
        switch ($this->columnTypes[$columnIndex]) {
            case DataType::NONE:
                return null;
            case DataType::LONG:
                return $columnValue->getIsNullvalues($rowIndex) ? null : $columnValue->getLongValues($rowIndex);
            case DataType::BOOLEAN:
                return $columnValue->getIsNullvalues($rowIndex) ? null : $columnValue->getBoolValues($rowIndex);
            case DataType::DOUBLE:
                return $columnValue->getIsNullvalues($rowIndex) ? null : $columnValue->getDoubleValues($rowIndex);
            case DataType::STRING:
                return $columnValue->getIsNullvalues($rowIndex) ? null : $columnValue->getStringValues($rowIndex);
            case DataType::BINARY:
                return $columnValue->getIsNullvalues($rowIndex) ? null : $columnValue->getBinaryValues($rowIndex);
            case DataType::STRING_RLE:
                // works on timeseries sql query for now
                // rle(run-length encoding) format, [a, a, a, b, c, d, a, a] would encode as
                // array: [a, b, c, d, a]
                // index_mapping: [0, 0, 0, 1, 2, 3, 4, 4]
                $rleStringValues = $columnValue->getRleStringValues();
                return $columnValue->getIsNullvalues($rowIndex) ? null : $rleStringValues->getArray($rleStringValues->getIndexMapping($rowIndex));
            default:
                throw new OTSClientException('unknown ColumnType type [' . $this->columnTypes[$columnIndex] . '].');
        }
    }

    public function getTableMeta() {
        return $this->sqlTableMeta;
    }

    public function getRowCount() {
        return $this->rowCount;
    }

    public function getColumnCount() {
        return $this->columnCount;
    }
}