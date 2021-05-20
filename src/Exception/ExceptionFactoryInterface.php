<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker\Exception;

use Exception;
use ReflectionException;

/**
 * Interface ExceptionFactoryInterface
 * @package Tleckie\CircuitBreaker\Exception
 */
interface ExceptionFactoryInterface
{
    /**
     * @param Serialized $serialized
     * @return Exception
     * @throws ReflectionException
     */
    public function create(Serialized $serialized): Exception;
}
