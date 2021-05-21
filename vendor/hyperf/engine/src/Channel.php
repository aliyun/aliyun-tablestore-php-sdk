<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Engine;

use Hyperf\Engine\Contract\ChannelInterface;
use Hyperf\Engine\Exception\RuntimeException;

class Channel extends \Swoole\Coroutine\Channel implements ChannelInterface
{
    /**
     * @var bool
     */
    protected $closed = false;

    public function getCapacity()
    {
        return $this->capacity;
    }

    public function getLength()
    {
        return $this->length();
    }

    public function isAvailable()
    {
        return ! $this->isClosing();
    }

    public function close()
    {
        $this->closed = true;
        parent::close();
    }

    public function hasProducers()
    {
        throw new RuntimeException('Not supported.');
    }

    public function hasConsumers()
    {
        throw new RuntimeException('Not supported.');
    }

    public function isReadable()
    {
        throw new RuntimeException('Not supported.');
    }

    public function isWritable()
    {
        throw new RuntimeException('Not supported.');
    }

    public function isClosing()
    {
        return $this->closed || $this->errCode === SWOOLE_CHANNEL_CLOSED;
    }

    public function isTimeout()
    {
        return ! $this->closed && $this->errCode === SWOOLE_CHANNEL_TIMEOUT;
    }
}
