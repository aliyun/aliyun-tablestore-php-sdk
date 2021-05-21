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

use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;

interface CoroutineInterface
{
    /**
     * @param callable $callable [required]
     */
    public function __construct(callable $callable);

    /**
     * @param mixed ...$data
     * @return $this
     */
    public function execute(...$data);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param callable $callable [required]
     * @param mixed ...$data
     * @return $this
     */
    public static function create(callable $callable, ...$data);

    /**
     * @return int returns coroutine id from current coroutine, -1 in non coroutine environment
     */
    public static function id();

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function pid(?int $id = null);

    /**
     * Set config to coroutine.
     */
    public static function set(array $config);

    /**
     * @param null|int $id coroutine id
     * @return null|\ArrayObject
     */
    public static function getContextFor(?int $id = null);

    /**
     * Execute callback when coroutine destruct.
     */
    public static function defer(callable $callable);
}
