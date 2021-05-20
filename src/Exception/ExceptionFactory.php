<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker\Exception;

use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class FactoryException
 * @package Tleckie\CircuitBreaker\Exception
 */
class ExceptionFactory implements ExceptionFactoryInterface
{
    /**
     * @param Serialized $serialized
     * @return Exception
     * @throws ReflectionException
     */
    public function create(Serialized $serialized): Exception
    {
        $className = $serialized->getClassName();

        $exception = new $className($serialized->getMessage(), $serialized->getCode());

        $traceProperty = (new ReflectionClass($exception))->getProperty('trace');

        $traceProperty->setAccessible(true);

        $traceProperty->setValue($exception, $serialized->getTrace());

        return $exception;
    }
}
