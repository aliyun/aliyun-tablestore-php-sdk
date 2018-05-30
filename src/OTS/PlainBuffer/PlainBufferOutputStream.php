<?php

namespace Aliyun\OTS\PlainBuffer;

class PlainBufferOutputStream
{
    var $curPos;
    var $capacity;
    var $buffer;

    public function __construct($capacity)
    {
        $this->curPos = 0;
        $this->capacity = $capacity;
        $this->buffer = str_repeat(chr(0), $this->capacity);
    }

    public function getBuffer()
    {
        return $this->buffer;
    }

    public function isFull()
    {
        return $this->curPos >= $this->capacity;
    }

    public function count()
    {
        return $this->curPos;
    }

    public function remain()
    {
        return $this->capacity - $this->count();
    }

    public function clear()
    {
        $this->curPos = 0;
    }

    public function writeRawByte($value)
    {
        if ($this->isFull()) {
            throw new \Aliyun\OTS\OTSClientException("The buffer is full");
        }
        $this->buffer[$this->curPos++] = chr($value);
    }

    public function writeRawLittleEndian32($value)
    {
        $this->writeBytes(pack("V", $value));
    }

    public function writeRawLittleEndian64($value)
    {
        $low = $value & 0xFFFFFFFF;
        $high = ($value >> 32) & 0xFFFFFFFF;

        $this->writeBytes(pack("V", $low));
        $this->writeBytes(pack("V", $high));
    }

    public function writeDouble($value)
    {
        $this->writeBytes(pack("d", $value));
    }

    public function writeBoolean($value)
    {
        $this->writeRawByte($value ? 1 : 0);
    }

    public function writeBytes($value)
    {
        $size = strlen($value);
        if ($this->curPos + $size > $this->capacity) {
            throw new \Aliyun\OTS\OTSClientException("The buffer is full");
        }
        for ($i = 0; $i < $size; $i++) {
            $this->buffer[$this->curPos++] = $value[$i];
        }
    }
}

