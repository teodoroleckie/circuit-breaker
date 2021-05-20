<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker;

use Exception;
use Psr\SimpleCache\CacheInterface;
use Tleckie\CircuitBreaker\Exception\CircuitBreakerException;
use Tleckie\CircuitBreaker\Exception\ExceptionFactory;
use Tleckie\CircuitBreaker\Exception\ExceptionFactoryInterface;
use Tleckie\CircuitBreaker\Exception\Serialized;
use function serialize;
use function sprintf;
use function unserialize;

/**
 * Class CircuitBreaker
 * @package Tleckie\CircuitBreaker
 */
class CircuitBreaker implements CircuitBreakerInterface
{
    /** @var string */
    protected const RETRY_KEY_FORMAT = '%s.retry';

    /** @var string */
    protected const EXCEPTION_KEY_FORMAT = '%s.serialized';

    /** @var CacheInterface */
    protected CacheInterface $cache;

    /** @var int */
    protected int $maxFailures;

    /** @var int */
    protected int $retryTimeout;

    /** @var ExceptionFactoryInterface */
    protected ExceptionFactoryInterface $factory;

    /**
     * CircuitBreaker constructor.
     * @param CacheInterface $cache
     * @param int $maxFailures
     * @param int $retryTimeout
     * @param ExceptionFactoryInterface|null $factory
     */
    public function __construct(
        CacheInterface $cache,
        int $maxFailures,
        int $retryTimeout,
        ExceptionFactoryInterface $factory = null
    ) {
        $this->cache = $cache;
        $this->maxFailures = $maxFailures;
        $this->retryTimeout = $retryTimeout;
        $this->factory = $factory ?? new ExceptionFactory();
    }

    /**
     * @inheritdoc
     */
    public function callService(callable $callable, string $serviceName): mixed
    {
        $exceptionKey = $this->exception($serviceName);

        if ($this->cache->has($exceptionKey) &&
            ($serialized = unserialize($this->cache->get($exceptionKey))) &&
            $serialized instanceof Serialized) {
            throw $this->factory->create($serialized);
        }

        try {
            return $callable();
        } catch (CircuitBreakerException $exception) {
            $this->halfOpen($exception->getException(), $serviceName);
        }
    }

    /**
     * @param string $serviceName
     * @return string
     */
    protected function exception(string $serviceName): string
    {
        return sprintf(static::EXCEPTION_KEY_FORMAT, strtolower($serviceName));
    }

    /**
     * @param Exception $exception
     * @param string $serviceName
     */
    protected function halfOpen(Exception $exception, string $serviceName)
    {
        $exceptionKey = $this->exception($serviceName);

        $retryKey = $this->retry($serviceName);

        $counter = 1;

        if ($this->cache->has($retryKey) && ($counter = $this->cache->get($retryKey)) && $counter) {
            $counter++;
        }

        $this->cache->set($retryKey, $counter, 60);

        if ($counter === $this->maxFailures) {
            $this->cache->delete($retryKey);
            $value = serialize(new Serialized($exception));
            $this->cache->set($exceptionKey, $value, $this->retryTimeout);
        }

        throw $exception;
    }

    /**
     * @param string $serviceName
     * @return string
     */
    protected function retry(string $serviceName): string
    {
        return sprintf(static::RETRY_KEY_FORMAT, strtolower($serviceName));
    }

    /**
     * @inheritdoc
     */
    public function setExceptionFactory(ExceptionFactoryInterface $exceptionFactory): CircuitBreakerInterface
    {
        $this->factory = $exceptionFactory;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExceptionFactory(): ExceptionFactoryInterface
    {
        return $this->factory;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(CacheInterface $cache): CircuitBreaker
    {
        $this->cache = $cache;

        return $this;
    }
}
