<?php

namespace Aliyun\OTS;

use Aliyun\OTS\Handlers\OTSHandlers;
use Aliyun\OTS\Handlers\RequestContext;

/**
 * Async response wrapper for OTS API calls.
 *
 * Returned by async methods (e.g. asyncSearch, asyncSqlQuery). The underlying HTTP
 * request is sent immediately and non-blocking. Call wait() to retrieve the result,
 * or access the response as an array directly — it will auto-resolve on first access.
 *
 * Implements ArrayAccess, Countable and IteratorAggregate so it can be used as a
 * transparent drop-in replacement for the synchronous response array.
 */
class AsyncResponse implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private RequestContext $context;
    private OTSHandlers $handlers;
    private bool $resolved = false;
    private ?array $response = null;
    private ?\Throwable $exception = null;

    public function __construct(RequestContext $context, OTSHandlers $handlers)
    {
        $this->context = $context;
        $this->handlers = $handlers;
    }

    /**
     * Resolve the async response. Returns cached result on subsequent calls.
     *
     * @return array
     * @throws \Throwable
     */
    private function resolve(): array
    {
        if ($this->resolved) {
            if ($this->exception !== null) {
                throw $this->exception;
            }
            return $this->response;
        }

        $this->resolved = true;

        try {
            $this->response = $this->handlers->resolveAsyncResponse($this->context);
            return $this->response;
        } catch (\Throwable $e) {
            $this->exception = $e;
            throw $e;
        }
    }

    /**
     * Wait for the async request to complete and return the response.
     *
     * @return array
     * @throws OTSClientException
     * @throws OTSServerException
     */
    public function wait(): array
    {
        return $this->resolve();
    }

    /**
     * @deprecated Use wait() instead. This method exists for backward compatibility
     *             with older SDK wrappers and will be removed in a future version.
     *
     * @return array
     * @throws OTSClientException
     * @throws OTSServerException
     */
    public function HWait(): array
    {
        return $this->resolve();
    }

    /**
     * Whether the async request has been resolved (either successfully or with an error).
     */
    public function isResolved(): bool
    {
        return $this->resolved;
    }

    /**
     * Whether the async request has failed. Triggers resolve if not yet resolved.
     */
    public function isFailed(): bool
    {
        if (!$this->resolved) {
            try {
                $this->resolve();
            } catch (\Throwable $e) {
            }
        }
        return $this->exception !== null;
    }

    /**
     * Whether the async request has succeeded. Triggers resolve if not yet resolved.
     */
    public function isSuccessful(): bool
    {
        if (!$this->resolved) {
            try {
                $this->resolve();
            } catch (\Throwable $e) {
            }
        }
        return $this->resolved && $this->exception === null;
    }

    /**
     * Get the exception if the request failed, or null if it succeeded.
     */
    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    // --- ArrayAccess ---

    public function offsetExists($offset): bool
    {
        return isset($this->resolve()[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->resolve()[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->resolve();
        $this->response[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        $this->resolve();
        unset($this->response[$offset]);
    }

    // --- Countable ---

    public function count(): int
    {
        return count($this->resolve());
    }

    // --- IteratorAggregate ---

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->resolve());
    }

    /**
     * Destructor — safely wait for the pending HTTP promise to prevent connection leaks.
     */
    public function __destruct()
    {
        if (!$this->resolved) {
            try {
                $this->context->httpPromise->wait();
            } catch (\Throwable $e) {
            }
        }
    }
}
