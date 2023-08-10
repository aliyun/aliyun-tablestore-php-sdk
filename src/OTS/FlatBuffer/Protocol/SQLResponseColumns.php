<?php
// automatically generated by the FlatBuffers compiler, do not modify

namespace Aliyun\OTS\FlatBuffer\Protocol;

use \Google\FlatBuffers\Struct;
use \Google\FlatBuffers\Table;
use \Google\FlatBuffers\ByteBuffer;
use \Google\FlatBuffers\FlatBufferBuilder;

class SQLResponseColumns extends Table
{
    /**
     * @param ByteBuffer $bb
     * @return SQLResponseColumns
     */
    public static function getRootAsSQLResponseColumns(ByteBuffer $bb)
    {
        $obj = new SQLResponseColumns();
        return ($obj->init($bb->getInt($bb->getPosition()) + $bb->getPosition(), $bb));
    }

    /**
     * @param int $_i offset
     * @param ByteBuffer $_bb
     * @return SQLResponseColumns
     **/
    public function init($_i, ByteBuffer $_bb)
    {
        $this->bb_pos = $_i;
        $this->bb = $_bb;
        return $this;
    }

    /**
     * @returnVectorOffset
     */
    public function getColumns($j)
    {
        $o = $this->__offset(4);
        $obj = new SQLResponseColumn();
        return $o != 0 ? $obj->init($this->__indirect($this->__vector($o) + $j * 4), $this->bb) : null;
    }

    /**
     * @return int
     */
    public function getColumnsLength()
    {
        $o = $this->__offset(4);
        return $o != 0 ? $this->__vector_len($o) : 0;
    }

    /**
     * @return long
     */
    public function getRowCount()
    {
        $o = $this->__offset(6);
        return $o != 0 ? $this->bb->getLong($o + $this->bb_pos) : 0;
    }

    /**
     * @param FlatBufferBuilder $builder
     * @return void
     */
    public static function startSQLResponseColumns(FlatBufferBuilder $builder)
    {
        $builder->StartObject(2);
    }

    /**
     * @param FlatBufferBuilder $builder
     * @return SQLResponseColumns
     */
    public static function createSQLResponseColumns(FlatBufferBuilder $builder, $columns, $row_count)
    {
        $builder->startObject(2);
        self::addColumns($builder, $columns);
        self::addRowCount($builder, $row_count);
        $o = $builder->endObject();
        return $o;
    }

    /**
     * @param FlatBufferBuilder $builder
     * @param VectorOffset
     * @return void
     */
    public static function addColumns(FlatBufferBuilder $builder, $columns)
    {
        $builder->addOffsetX(0, $columns, 0);
    }

    /**
     * @param FlatBufferBuilder $builder
     * @param array offset array
     * @return int vector offset
     */
    public static function createColumnsVector(FlatBufferBuilder $builder, array $data)
    {
        $builder->startVector(4, count($data), 4);
        for ($i = count($data) - 1; $i >= 0; $i--) {
            $builder->putOffset($data[$i]);
        }
        return $builder->endVector();
    }

    /**
     * @param FlatBufferBuilder $builder
     * @param int $numElems
     * @return void
     */
    public static function startColumnsVector(FlatBufferBuilder $builder, $numElems)
    {
        $builder->startVector(4, $numElems, 4);
    }

    /**
     * @param FlatBufferBuilder $builder
     * @param long
     * @return void
     */
    public static function addRowCount(FlatBufferBuilder $builder, $rowCount)
    {
        $builder->addLongX(1, $rowCount, 0);
    }

    /**
     * @param FlatBufferBuilder $builder
     * @return int table offset
     */
    public static function endSQLResponseColumns(FlatBufferBuilder $builder)
    {
        $o = $builder->endObject();
        return $o;
    }

    public static function finishSQLResponseColumnsBuffer(FlatBufferBuilder $builder, $offset)
    {
        $builder->finish($offset);
    }
}