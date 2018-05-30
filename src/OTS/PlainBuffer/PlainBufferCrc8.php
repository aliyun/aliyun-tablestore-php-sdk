<?php


namespace Aliyun\OTS\PlainBuffer;
use Aliyun\OTS\OTSClientException;

/**
 * 采用crc-8-ATM规范
 * 多项式: x^8 + x^2 + x + 1
 *
 */
class PlainBufferCrc8
{
    private static $crc8Table;
    private static $spaceSize = 256;

    public static function toByte($num) {
        $num &= 0xff;
        if($num > 128) {
            $num = $num -256;
        }
        return $num;
    }

    public static function initCrCTable() {
        for ($i = 0; $i < self::$spaceSize; ++$i) {
            $x = self::toByte($i);
            for ($j = 8; $j > 0; --$j) {
                $x = self::toByte(($x << 1) ^ ((($x & 0x80) != 0) ? 0x07 : 0));
            }
            self::$crc8Table[$i] = $x;
        }
    }

    public static function crcInt8($crc, $byte)
    {
        $crc = self::$crc8Table[($crc ^ self::toByte($byte)) & 0xff];
        return $crc;
    }

    public static function crcInt32($crc, $byte)
    {
        for($i = 0; $i < 4; $i++) {
            $crc = self::crcInt8($crc, ($byte >> ($i * 8)) & 0xff);
        }
        return $crc;
    }

    public static function crcInt64($crc, $value)
    {
        $low = $value & 0xFFFFFFFF;
        $high = ($value >> 32) & 0xFFFFFFFF;
        $crc = self::crcInt32($crc, $low);
        $crc = self::crcInt32($crc, $high);
        return $crc;
    }

    public static function crcDouble($crc, $value)
    {
        $data = pack("d", $value);
        for($i = 0; $i < 8; $i++) {
            $crc = self::crcInt8($crc, ord($data[$i]));
        }
        return $crc;
    }

    public static function crcString($crc, $byte)
    {
        if(!is_string($byte)) {
            throw new OTSClientException("must be string, actual:" . gettype($byte));
        }
        for($i = 0; $i < strlen($byte); $i++) {
            $crc = self::crcInt8($crc, ord($byte[$i]));
        }
        return $crc;
    }
}

PlainBufferCrc8::initCrCTable();


