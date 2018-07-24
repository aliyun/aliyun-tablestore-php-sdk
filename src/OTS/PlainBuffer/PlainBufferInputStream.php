<?php

namespace Aliyun\OTS\PlainBuffer;


class PlainBufferInputStream
{
    var $buffer;
    var $curPos;
    var $lastTag;
    var $len;

    public function __construct($buffer)
    {
        $this->buffer = $buffer;
        $this->curPos = 0;
        $this->lastTag = 0;
        $this->len = strlen($this->buffer);
    }

    public function isAtEnd()
    {
        return $this->len == $this->curPos;
    }

    public function readTag()
    {
        if ($this->isAtEnd()) {
            $this->lastTag = 0;
            return 0;
        }
        $this->lastTag = PlainBufferCrc8::toByte(ord($this->readRawByte()));
        return $this->lastTag;
    }

    public function checkLastTagWas($tag)
    {
        //return ($this->lastTag) == $tag;
        return $this->lastTag == $tag;
    }

    public function getLastTag()
    {
        return $this->lastTag;
    }

    public function readRawByte()
    {
        if ($this->isAtEnd()) {
            throw new \Aliyun\OTS\OTSClientException("Read raw byte encountered EOF.");
        }
        $pos = $this->curPos;
        $this->curPos += 1;
        return $this->buffer[$pos];
    }

    public function readRawLittleEndian64()
    {
        return unpack('P', $this->readBytes(8))[1];
    }

    public function readRawLittleEndian32()
    {
        return unpack('V', $this->readBytes(4))[1];
    }

    public function readBoolean()
    {
        return ord($this->readRawByte()) != 0;
    }

    public function readDouble()
    {
        return unpack('d', $this->readBytes(8))[1];
    }

    public function readInt64()
    {
        $low = unpack('V', $this->readBytes(4))[1];
        $high = unpack('V', $this->readBytes(4))[1];
        return  ($high << 32) | $low;
    }

    public function readInt32()
    {
        return unpack('V', $this->readBytes(4))[1];
    }

    public function readBytes($size)
    {
        if (strlen($this->buffer) - $this->curPos < $size) {
            throw new \Aliyun\OTS\OTSClientException("Read bytes encountered EOF.");
        }
        $tmpPos = $this->curPos;
        $this->curPos += $size;
        return substr($this->buffer, $tmpPos, $size);
    }

    public function readUtfString($size)
    {
        return self::readBytes($size);
    }

    public function skipRawSize($size) {
        self::readBytes($size);
    }
}

