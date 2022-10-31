<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table_store_search.proto

namespace Aliyun\OTS\ProtoBuffer\Protocol;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>aliyun.OTS.ProtoBuffer.Protocol.GroupByRange</code>
 */
class GroupByRange extends \Aliyun\OTS\ProtoBuffer\Protocol\Message
{
    /**
     * Generated from protobuf field <code>optional string field_name = 1;</code>
     */
    private $field_name = '';
    private $has_field_name = false;
    /**
     * Generated from protobuf field <code>repeated .aliyun.OTS.ProtoBuffer.Protocol.Range ranges = 2;</code>
     */
    private $ranges;
    private $has_ranges = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Aggregations sub_aggs = 3;</code>
     */
    private $sub_aggs = null;
    private $has_sub_aggs = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.GroupBys sub_group_bys = 4;</code>
     */
    private $sub_group_bys = null;
    private $has_sub_group_bys = false;

    public function __construct() {
        \GPBMetadata\TableStoreSearch::initOnce();
        parent::__construct();
    }

    /**
     * Generated from protobuf field <code>optional string field_name = 1;</code>
     * @return string
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * Generated from protobuf field <code>optional string field_name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setFieldName($var)
    {
        GPBUtil::checkString($var, True);
        $this->field_name = $var;
        $this->has_field_name = true;

        return $this;
    }

    public function hasFieldName()
    {
        return $this->has_field_name;
    }

    /**
     * Generated from protobuf field <code>repeated .aliyun.OTS.ProtoBuffer.Protocol.Range ranges = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getRanges()
    {
        return $this->ranges;
    }

    /**
     * Generated from protobuf field <code>repeated .aliyun.OTS.ProtoBuffer.Protocol.Range ranges = 2;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\Range[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setRanges($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Aliyun\OTS\ProtoBuffer\Protocol\Range::class);
        $this->ranges = $arr;
        $this->has_ranges = true;

        return $this;
    }

    public function hasRanges()
    {
        return $this->has_ranges;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Aggregations sub_aggs = 3;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\Aggregations
     */
    public function getSubAggs()
    {
        return $this->sub_aggs;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Aggregations sub_aggs = 3;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\Aggregations $var
     * @return $this
     */
    public function setSubAggs($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\Aggregations::class);
        $this->sub_aggs = $var;
        $this->has_sub_aggs = true;

        return $this;
    }

    public function hasSubAggs()
    {
        return $this->has_sub_aggs;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.GroupBys sub_group_bys = 4;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\GroupBys
     */
    public function getSubGroupBys()
    {
        return $this->sub_group_bys;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.GroupBys sub_group_bys = 4;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\GroupBys $var
     * @return $this
     */
    public function setSubGroupBys($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\GroupBys::class);
        $this->sub_group_bys = $var;
        $this->has_sub_group_bys = true;

        return $this;
    }

    public function hasSubGroupBys()
    {
        return $this->has_sub_group_bys;
    }

}

