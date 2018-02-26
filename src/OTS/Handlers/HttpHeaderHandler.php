<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS;
use Aliyun\OTS\OTSClientException as OTSClientException;

class HttpHeaderHandler
{

    private function makeHeaderString($headers)
    {
        $headerArray = array();
        foreach ($headers as $key => $value) {
            if (substr($key, 0, 5) == 'x-ots' && $key != 'x-ots-signature') {
                array_push($headerArray, "{$key}:{$value}");
            }
        }
        sort($headerArray);

        return implode("\n", $headerArray);
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
        $timestamp = gmdate('D, d M Y H:i:s \G\M\T');

        $headers = array(
            "x-ots-accesskeyid" => $context->clientConfig->getAccessKeyID(),
            "x-ots-apiversion" => "2014-08-08",
            "x-ots-contentmd5" => base64_encode(md5($context->requestBody, TRUE)),
            "x-ots-date" => $timestamp,
            "x-ots-instancename" => $context->clientConfig->getInstanceName(),
            "User-Agent" => "aliyun-sdk-php 1.0.0",
        );

        $signature = $this->computeRequestSignature($context, $headers);
        $headers["x-ots-signature"] = $signature;
        $context->requestHeaders = $headers;
    }

    private function checkOtherHeaders($context)
    {
        // Step 1, make sure we have all headers
        $headerNames = array(
            "x-ots-contentmd5",
            "x-ots-requestid",
            "x-ots-date",
            "x-ots-contenttype",
        );

        if ($context->responseHttpStatus >= 200 && $context->responseHttpStatus < 300) {
            foreach ($headerNames as $name) {
                if (!isset($context->responseHeaders[$name])) {
                    throw new OTSClientException("$name is missing in response header.");
                }
            }
        }

        // Step 2, check md5
        if (isset($context->responseHeaders['x-ots-contentmd5'])) {
            $expectMD5 = base64_encode(md5($context->responseBody, TRUE));
            if ($expectMD5 != $context->responseHeaders['x-ots-contentmd5']) {
                throw new OTSClientException("MD5 mismatch in response.");
            }
        }

        // Step 3, check date
        if (isset($context->responseHeaders['x-ots-date'])) {
            $serverTimeStr = $context->responseHeaders['x-ots-date'];
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
        if (!isset($context->responseHeaders['Authorization'])) {
            if ($context->responseHttpStatus >= 200 && $context->responseHttpStatus < 300) {
                throw new OTSClientException("\"Authorization\" is missing in response header.");
            }
        }
        $authorization = $context->responseHeaders['Authorization'];

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
}

