<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;
use Aliyun\OTS\OTSClientException as OTSClientException;

// refer: https://help.aliyun.com/document_detail/27299.html
class HttpHeaderHandler
{

    private function makeHeaderString($headers)
    {
        $headerArray = array();
        foreach ($headers as $key => $value) {
            if (substr($key, 0, 5) == self::OTS_PREFIX && $key != self::OTS_SIGNATURE) {
                array_push($headerArray, "{$key}:{$value}");
            }
        }
        sort($headerArray);

        return implode("\n", $headerArray);
    }

    /**
     * Generates UserAgent
     *
     * @return string
     */
    private function generateUserAgent()
    {
        return self::OTS_NAME . "/" . self::OTS_VERSION . " (" . php_uname('s') . "/" . php_uname('r') . "/" . php_uname('m') . ";" . PHP_VERSION . ")";
    }

    private function computeRequestSignature($context, $headers)
    {
        $canonicalHeaders = $this->makeHeaderString($headers);
        $stringToSign = "/" . $context->apiName . "\nPOST\n\n" . $canonicalHeaders . "\n";
        $signature = hash_hmac("sha1", $stringToSign, $context->clientConfig->getAccessKeySecret(), TRUE);
        return base64_encode($signature);
    }

    private function computeResponseSignature($context, $headers)
    {
        $canonicalHeaders = $this->makeHeaderString($headers);
        $stringToSign = $canonicalHeaders . "\n/" . $context->apiName;
        $signature = hash_hmac("sha1", $stringToSign, $context->clientConfig->getAccessKeySecret(), TRUE);
        return base64_encode($signature);
    }


    public function handleBefore($context)
    {
        // time() - date('Z') ensures UTC+0 timestamp in any environment
        // but we will still get the warning:
        // "date(): It is not safe to rely on the system's timezone settings."
        // it's up to the user to decide the timezone.
        $timestamp = gmdate('Y-m-d\TH:i:s.000\Z');

        $headers = array(
            self::OTS_ACCESS_KEY_ID => $context->clientConfig->getAccessKeyID(),
            self::OTS_API_VERSION => self::OTS_BUILD,
            self::OTS_CONTENT_MD5 => base64_encode(md5($context->requestBody, TRUE)),
            self::OTS_DATE => $timestamp,
            self::OTS_INSTANCE_NAME => $context->clientConfig->getInstanceName()
        );

        if ($context->clientConfig->getStsToken() != null) {
            $headers[self::OTS_STSTOKEN] = $context->clientConfig->getStsToken();
        }

        $signature = $this->computeRequestSignature($context, $headers);
        $headers[self::OTS_SIGNATURE] = $signature;
        $headers[self::USER_AGENT] = self::generateUserAgent();
        $context->requestHeaders = $headers;
    }

    private function checkOtherHeaders($context)
    {
        // Step 1, make sure we have all headers
        $headerNames = array(
            self::OTS_CONTENT_MD5,
            self::OTS_REQUEST_ID,
            self::OTS_DATE,
            self::OTS_CONTENT_TYPE,
        );

        if ($context->responseHttpStatus >= 200 && $context->responseHttpStatus < 300) {
            foreach ($headerNames as $name) {
                if (!isset($context->responseHeaders[$name])) {
                    throw new OTSClientException("$name is missing in response header.");
                }
            }
        }

        // Step 2, check md5
        if (isset($context->responseHeaders[self::OTS_CONTENT_MD5])) {
            $expectMD5 = base64_encode(md5($context->responseBody, TRUE));
            if ($expectMD5 != $context->responseHeaders[self::OTS_CONTENT_MD5]) {
                throw new OTSClientException("MD5 mismatch in response.");
            }
        }

        // Step 3, check date
        if (isset($context->responseHeaders[self::OTS_DATE])) {
            $serverTimeStr = $context->responseHeaders[self::OTS_DATE];
            $serverTime = strtotime($serverTimeStr);
            if ($serverTime == false) {
                throw new OTSClientException("Invalid date format in response: $serverTimeStr");
            }
            $clientTime = time();

            if (abs($clientTime - $serverTime) > 15 * 60) {
                throw new OTSClientException("The difference between date in response and system time is more than 15 minutes.");
            }
        }

    }

    private function checkAuthorization($context)
    {
        // Step 1, Check if authorization header is there
        if (!isset($context->responseHeaders[self::OTS_AUTHORIZATION]) && !isset($context->responseHeaders[self::OTS_AUTHORIZATION_LOWER])) {
            if ($context->responseHttpStatus >= 200 && $context->responseHttpStatus < 300) {
                throw new OTSClientException("\"Authorization\" is missing in response header.");
            }
        }
        if (!isset($context->responseHeaders[self::OTS_AUTHORIZATION_LOWER])) {
            $authorization = $context->responseHeaders[self::OTS_AUTHORIZATION];
        } else {
            $authorization = $context->responseHeaders[self::OTS_AUTHORIZATION_LOWER];
        }

        // Step 2, check if authorization is valid
        if (substr($authorization, 0, 4) != "OTS ") {
            throw new OTSClientException("Invalid Authorization in response. Authorization: " . $authorization);
        }
        $splits = explode(":", substr($authorization, 4));
        if (count($splits) != 2) {
            throw new OTSClientException("Invalid Authorization in response.");
        }
        $accessKeyID = $splits[0];
        $signature = $splits[1];
        
        // Step 3, check accessKeyID
        if ($accessKeyID != $context->clientConfig->getAccessKeyID()) {
            throw new OTSClientException("Access Key ID mismatch in response.");
        }
        
        // Step 4, check signature
        if ($signature != $this->computeResponseSignature($context, $context->responseHeaders)) {
            throw new OTSClientException("Signature mismatch in response.");
        }
    }

    public function handleAfter($context)
    {
        try {
            $this->checkOtherHeaders($context);

            // header 'authorization' is not neccessarily available 
            // when HttpStatus == 403 (Forbidden).
            // but if it is availabe, we still have to check it
            if ($context->responseHttpStatus != 403) {
                $this->checkAuthorization($context);
            }
        } catch (OTSClientException $e) {
            $errorLogger = $context->clientConfig->errorLogHandler;

            if ($errorLogger != null) {
                $errorLogger("$context->apiName HttpHeaders: " . json_encode($context->responseHeaders));
            }

            // re-throw the exception with additonal information
            throw new OTSClientException((string)$e . " Http Status: " . $context->responseHttpStatus);
        }
    }

    // OTSClient version information
    const OTS_NAME = "aliyun-tablestore-sdk-php";
    const OTS_VERSION = "4.1.0";
    const OTS_BUILD = "2015-12-31";

    // OTS Internal constants
    const OTS_PREFIX = 'x-ots';
    const OTS_ACCESS_KEY_ID = "x-ots-accesskeyid";
    const OTS_API_VERSION = "x-ots-apiversion";
    const OTS_CONTENT_MD5 = "x-ots-contentmd5";
    const OTS_DATE = "x-ots-date";
    const OTS_INSTANCE_NAME = "x-ots-instancename";
    const OTS_STSTOKEN = 'x-ots-ststoken';
    const OTS_SIGNATURE = "x-ots-signature";
    const OTS_REQUEST_ID = "x-ots-requestid";
    const OTS_CONTENT_TYPE = "x-ots-contenttype";

    // other const
    const USER_AGENT = "User-Agent";
    const OTS_AUTHORIZATION = 'Authorization';
    const OTS_AUTHORIZATION_LOWER = 'authorization';
}

