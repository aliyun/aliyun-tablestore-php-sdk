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
namespace Hyperf\Engine\Contract\Http;

use Hyperf\Engine\Http\RawResponse;

interface ClientInterface
{
    public function __construct(string $name, int $port, bool $ssl = false);

    /**
     * @return $this
     */
    public function set(array $settings);

    /**
     * @param string[][] $headers
     */
    public function request(string $method = 'GET', string $path = '/', array $headers = [], string $contents = '', string $version = '1.1'): RawResponse;
}
