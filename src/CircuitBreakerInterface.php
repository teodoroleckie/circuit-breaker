<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker;

use Exception;
use Psr\SimpleCache\CacheInterface;
use Tleckie\CircuitBreaker\Exception\CircuitBreakerException;
use Tleckie\CircuitBreaker\Exception\ExceptionFactoryInterface;

/**
 * Interface ProxyInterface
 * @package Tleckie\CircuitBreaker
 */
interface CircuitBreakerInterface
{
    /**
     * @param callable $callable
     * @param string $serviceName
     * @return mixed
     *
     * @throws CircuitBreakerException|Exception
     */
    public function callService(callable $callable, string $serviceName): mixed;

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @return CircuitBreakerInterface
     */
    public function setExceptionFactory(ExceptionFactoryInterface $exceptionFactory): CircuitBreakerInterface;

    /**
     * @return ExceptionFactoryInterface
     */
    public function getExceptionFactory(): ExceptionFactoryInterface;

    /**
     * @param CacheInterface $cache
     * @return CircuitBreaker
     */
    public function setCache(CacheInterface $cache): CircuitBreaker;

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface;
}
