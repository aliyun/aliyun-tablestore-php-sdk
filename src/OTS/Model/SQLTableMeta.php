<?php

namespace Aliyun\OTS\Model;

use Aliyun\OTS\FlatBuffer\Protocol\SQLResponseColumns;

class SQLTableMeta
{
    private $sqlTableSchema = array();

    public function __construct(array $schemas)
    {
        $this->sqlTableSchema = $schemas;
    }

    /**
     * @param string offset
     * */
    public function getSchemaByColumnName($columnName) {
        for ($i = 0; $i < count($this->sqlTableSchema); $i++) {
            if ($this->sqlTableSchema[$i]['name'] == $columnName) {
                return $this->sqlTableSchema[$i];
            }
        }
        return null;
    }

    /**
     * @param int offset
     * */
    public function getSchemaByIndex($index) {
        return $this->sqlTableSchema[$index];
    }

    public function getSchemas() {
        return $this->sqlTableSchema;
    }
}