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
namespace Hyperf\Engine\Contract;

interface ChannelInterface
{
    /**
     * @param mixed $data [required]
     * @param float|int $timeout [optional] = -1
     * @return bool
     */
    public function push($data, $timeout = -1);

    /**
     * @param float $timeout seconds [optional] = -1
     * @return mixed when pop failed, return false
     */
    public function pop($timeout = -1);

    /**
     * @return mixed
     */
    public function close();

    /**
     * @return int
     */
    public function getCapacity();

    /**
     * @return int
     */
    public function getLength();

    /**
     * @return bool
     */
    public function isAvailable();

    /**
     * @return bool
     */
    public function hasProducers();

    /**
     * @return bool
     */
    public function hasConsumers();

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return bool
     */
    public function isFull();

    /**
     * @return bool
     */
    public function isReadable();

    /**
     * @return bool
     */
    public function isWritable();

    /**
     * @return bool
     */
    public function isClosing();

    /**
     * @return bool
     */
    public function isTimeout();
}
