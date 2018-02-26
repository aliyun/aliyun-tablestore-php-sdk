<?php

namespace Aliyun\OTS;

/**
 * OTSServerException 是 OTS SDK遇到OTS服务端返回错误时抛出的异常，
 * 包含 HTTP状态码， OTS Error Code， OTS Error Message，以及 Request ID。
 */
class OTSServerException extends \Exception {

    private $otsErrorMsg;
    private $otsErrorCode;
    private $requestId;
    private $httpStatus;
    private $apiName;

    public function __construct($apiName, $httpStatus, $otsErrorCode = null, $otsErrorMsg = null, $requestId = null) 
    {
        $this->apiName = $apiName;
        $this->otsErrorMsg = $otsErrorMsg;
        $this->otsErrorCode = $otsErrorCode;
        $this->requestId = $requestId;
        $this->httpStatus = $httpStatus;
        parent::__construct($this->__toString());
    }

    public function getOTSErrorMessage() 
    {
        return $this->otsErrorMsg;
    }

    public function getOTSErrorCode()
    {
        return $this->otsErrorCode;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function __toString()
    {
        return "API: {$this->apiName}, HttpStatus: {$this->httpStatus}, OTSErrorCode: {$this->otsErrorCode}, OTSErrorMsg: {$this->otsErrorMsg}, RequestId: {$this->requestId}";
    }
}

