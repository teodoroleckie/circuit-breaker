<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker\Tests\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Tleckie\CircuitBreaker\Exception\CircuitBreakerException;

/**
 * Class CircuitBreakerExceptionTest
 * @package Tleckie\CircuitBreaker\Tests\Exception
 */
class CircuitBreakerExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function parent(): void
    {
        $exception = new CircuitBreakerException(new Exception('test', 500));

        static::assertEquals('test', $exception->getMessage());

        static::assertEquals(500, $exception->getCode());
    }
}
