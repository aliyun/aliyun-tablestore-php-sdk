<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table_store_search.proto

namespace Aliyun\OTS\ProtoBuffer\Protocol;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>aliyun.OTS.ProtoBuffer.Protocol.ComputeSplitsResponse</code>
 */
class ComputeSplitsResponse extends \Aliyun\OTS\ProtoBuffer\Protocol\Message
{
    /**
     * Generated from protobuf field <code>optional bytes session_id = 1;</code>
     */
    private $session_id = '';
    private $has_session_id = false;
    /**
     * Generated from protobuf field <code>optional int32 splits_size = 2;</code>
     */
    private $splits_size = 0;
    private $has_splits_size = false;

    public function __construct() {
        \GPBMetadata\TableStoreSearch::initOnce();
        parent::__construct();
    }

    /**
     * Generated from protobuf field <code>optional bytes session_id = 1;</code>
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Generated from protobuf field <code>optional bytes session_id = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSessionId($var)
    {
        GPBUtil::checkString($var, False);
        $this->session_id = $var;
        $this->has_session_id = true;

        return $this;
    }

    public function hasSessionId()
    {
        return $this->has_session_id;
    }

    /**
     * Generated from protobuf field <code>optional int32 splits_size = 2;</code>
     * @return int
     */
    public function getSplitsSize()
    {
        return $this->splits_size;
    }

    /**
     * Generated from protobuf field <code>optional int32 splits_size = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setSplitsSize($var)
    {
        GPBUtil::checkInt32($var);
        $this->splits_size = $var;
        $this->has_splits_size = true;

        return $this;
    }

    public function hasSplitsSize()
    {
        return $this->has_splits_size;
    }

}

