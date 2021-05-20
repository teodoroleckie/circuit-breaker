<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker\Exception;

use Exception;
use Throwable;

/**
 * Class UnavailableServiceException
 * @package Tleckie\CircuitBreaker
 */
class CircuitBreakerException extends Exception
{
    /**
     * @var Throwable
     */
    protected Throwable $exception;

    /**
     * CircuitBreakerException constructor.
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        parent::__construct($exception->getMessage(), $exception->getCode(), $exception);

        $this->exception = $exception;
    }

    /**
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }
}
