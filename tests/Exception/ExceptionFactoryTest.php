<?php

namespace Tleckie\CircuitBreaker\Tests\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tleckie\CircuitBreaker\Exception\ExceptionFactory;
use Tleckie\CircuitBreaker\Exception\Serialized;

/**
 * Class ExceptionFactoryTest
 * @package Tleckie\CircuitBreaker\Tests\Exception
 */
class ExceptionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function create(): void
    {
        $factory = new ExceptionFactory();

        $exception = new Exception('Test message', 500);

        $serialized = new Serialized($exception);
        $serialized->setTrace(['test']);
        $serialized->setTrace(['test']);

        $exceptionWithTrace = $factory->create($serialized);

        $traceProperty = (new ReflectionClass($exceptionWithTrace))->getProperty('trace');

        static::assertTrue($traceProperty->isPrivate());

        static::assertEquals('Test message', $exceptionWithTrace->getMessage());

        static::assertEquals(500, $exceptionWithTrace->getCode());

        static::assertTrue(['test'] === $exceptionWithTrace->getTrace());
    }
}
