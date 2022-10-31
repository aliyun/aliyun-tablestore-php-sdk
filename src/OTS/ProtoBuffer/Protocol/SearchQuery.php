<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table_store_search.proto

namespace Aliyun\OTS\ProtoBuffer\Protocol;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>aliyun.OTS.ProtoBuffer.Protocol.SearchQuery</code>
 */
class SearchQuery extends \Aliyun\OTS\ProtoBuffer\Protocol\Message
{
    /**
     * Generated from protobuf field <code>optional int32 offset = 1;</code>
     */
    private $offset = 0;
    private $has_offset = false;
    /**
     * Generated from protobuf field <code>optional int32 limit = 2;</code>
     */
    private $limit = 0;
    private $has_limit = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Query query = 4;</code>
     */
    private $query = null;
    private $has_query = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Collapse collapse = 5;</code>
     */
    private $collapse = null;
    private $has_collapse = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Sort sort = 6;</code>
     */
    private $sort = null;
    private $has_sort = false;
    /**
     * Generated from protobuf field <code>optional bool getTotalCount = 8;</code>
     */
    private $getTotalCount = false;
    private $has_getTotalCount = false;
    /**
     * Generated from protobuf field <code>optional bytes token = 9;</code>
     */
    private $token = '';
    private $has_token = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Aggregations aggs = 10;</code>
     */
    private $aggs = null;
    private $has_aggs = false;
    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.GroupBys group_bys = 11;</code>
     */
    private $group_bys = null;
    private $has_group_bys = false;

    public function __construct() {
        \GPBMetadata\TableStoreSearch::initOnce();
        parent::__construct();
    }

    /**
     * Generated from protobuf field <code>optional int32 offset = 1;</code>
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Generated from protobuf field <code>optional int32 offset = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setOffset($var)
    {
        GPBUtil::checkInt32($var);
        $this->offset = $var;
        $this->has_offset = true;

        return $this;
    }

    public function hasOffset()
    {
        return $this->has_offset;
    }

    /**
     * Generated from protobuf field <code>optional int32 limit = 2;</code>
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Generated from protobuf field <code>optional int32 limit = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setLimit($var)
    {
        GPBUtil::checkInt32($var);
        $this->limit = $var;
        $this->has_limit = true;

        return $this;
    }

    public function hasLimit()
    {
        return $this->has_limit;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Query query = 4;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Query query = 4;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\Query $var
     * @return $this
     */
    public function setQuery($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\Query::class);
        $this->query = $var;
        $this->has_query = true;

        return $this;
    }

    public function hasQuery()
    {
        return $this->has_query;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Collapse collapse = 5;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\Collapse
     */
    public function getCollapse()
    {
        return $this->collapse;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Collapse collapse = 5;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\Collapse $var
     * @return $this
     */
    public function setCollapse($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\Collapse::class);
        $this->collapse = $var;
        $this->has_collapse = true;

        return $this;
    }

    public function hasCollapse()
    {
        return $this->has_collapse;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Sort sort = 6;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\Sort
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Sort sort = 6;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\Sort $var
     * @return $this
     */
    public function setSort($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\Sort::class);
        $this->sort = $var;
        $this->has_sort = true;

        return $this;
    }

    public function hasSort()
    {
        return $this->has_sort;
    }

    /**
     * Generated from protobuf field <code>optional bool getTotalCount = 8;</code>
     * @return bool
     */
    public function getGetTotalCount()
    {
        return $this->getTotalCount;
    }

    /**
     * Generated from protobuf field <code>optional bool getTotalCount = 8;</code>
     * @param bool $var
     * @return $this
     */
    public function setGetTotalCount($var)
    {
        GPBUtil::checkBool($var);
        $this->getTotalCount = $var;
        $this->has_getTotalCount = true;

        return $this;
    }

    public function hasGetTotalCount()
    {
        return $this->has_getTotalCount;
    }

    /**
     * Generated from protobuf field <code>optional bytes token = 9;</code>
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Generated from protobuf field <code>optional bytes token = 9;</code>
     * @param string $var
     * @return $this
     */
    public function setToken($var)
    {
        GPBUtil::checkString($var, False);
        $this->token = $var;
        $this->has_token = true;

        return $this;
    }

    public function hasToken()
    {
        return $this->has_token;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Aggregations aggs = 10;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\Aggregations
     */
    public function getAggs()
    {
        return $this->aggs;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.Aggregations aggs = 10;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\Aggregations $var
     * @return $this
     */
    public function setAggs($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\Aggregations::class);
        $this->aggs = $var;
        $this->has_aggs = true;

        return $this;
    }

    public function hasAggs()
    {
        return $this->has_aggs;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.GroupBys group_bys = 11;</code>
     * @return \Aliyun\OTS\ProtoBuffer\Protocol\GroupBys
     */
    public function getGroupBys()
    {
        return $this->group_bys;
    }

    /**
     * Generated from protobuf field <code>optional .aliyun.OTS.ProtoBuffer.Protocol.GroupBys group_bys = 11;</code>
     * @param \Aliyun\OTS\ProtoBuffer\Protocol\GroupBys $var
     * @return $this
     */
    public function setGroupBys($var)
    {
        GPBUtil::checkMessage($var, \Aliyun\OTS\ProtoBuffer\Protocol\GroupBys::class);
        $this->group_bys = $var;
        $this->has_group_bys = true;

        return $this;
    }

    public function hasGroupBys()
    {
        return $this->has_group_bys;
    }

}

