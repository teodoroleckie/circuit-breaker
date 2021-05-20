<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker\Tests;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Tleckie\CircuitBreaker\CircuitBreaker;
use Tleckie\CircuitBreaker\Exception\CircuitBreakerException;
use Tleckie\CircuitBreaker\Exception\ExceptionFactoryInterface;

/**
 * Class CircuitBreakerTest
 * @package Tleckie\CircuitBreaker\Tests\Exception
 */
class CircuitBreakerTest extends TestCase
{
    /** @var CacheInterface|MockObject */
    protected CacheInterface|MockObject $cacheMock;

    /** @var ExceptionFactoryInterface|MockObject */
    protected ExceptionFactoryInterface|MockObject $factoryMock;

    /** @var CircuitBreaker */
    protected CircuitBreaker $circuitBreaker;

    /**
     * @test
     */
    public function closed(): void
    {
        $value = $this->circuitBreaker->callService(static function () {
            return true;
        }, 'service');

        static::assertTrue($value);
    }

    /**
     * @test
     */
    public function exceptionFactory(): void
    {
        static::assertTrue($this->circuitBreaker->getExceptionFactory() === $this->factoryMock);
    }

    /**
     * @test
     */
    public function opened(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('test');

        $serializedException = 'C:43:"Tleckie\CircuitBreaker\Exception\Serialized":98:{a:4:{s:7:"message";s:4:"test";s:4:"code";i:500;s:9:"className";s:9:"Exception";s:5:"trace";a:0:{}}}';

        $this->cacheMock
            ->expects(static::once())
            ->method('has')
            ->willReturn(true);

        $this->cacheMock
            ->expects(static::once())
            ->method('get')
            ->willReturn($serializedException);

        $this->factoryMock
            ->expects(static::once())
            ->method('create')
            ->willReturn(new Exception('test', 500));

        $this->circuitBreaker->callService(static function () {
            return true;
        }, 'SERVICE');
    }

    /**
     * @test
     */
    public function notValidSerializedObject(): void
    {
        $serializedException = '';

        $this->cacheMock
            ->expects(static::once())
            ->method('has')
            ->with('service.serialized')
            ->willReturn(true);

        $this->cacheMock
            ->expects(static::once())
            ->method('get')
            ->with('service.serialized')
            ->willReturn($serializedException);

        $value = $this->circuitBreaker->callService(static function () {
            return true;
        }, 'SERVICE');

        static::assertTrue($value);
    }

    /**
     * @test
     */
    public function error(): void
    {
        $serializedException = 'O:8:"stdClass":0:{}';

        $this->cacheMock
            ->expects(static::once())
            ->method('has')
            ->willReturn(true);

        $this->cacheMock
            ->expects(static::once())
            ->method('get')
            ->willReturn($serializedException);

        $value = $this->circuitBreaker->callService(static function () {
            return true;
        }, 'SERVICE');

        static::assertTrue($value);
    }

    /**
     * @test
     */
    public function counter(): void
    {
        $this->circuitBreaker = new CircuitBreaker(
            $this->cacheMock,
            2,
            3
        );

        $this->cacheMock
            ->method('has')
            ->withConsecutive(
                ['service.serialized'],
                ['service.retry'],
                ['service.serialized'],
                ['service.retry']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                false,
                true
            );

        $this->cacheMock
            ->expects(self::exactly(1))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                "1"
            );

        $this->cacheMock
            ->expects(self::exactly(4))
            ->method('set');

        $this->cacheMock
            ->expects(self::exactly(1))
            ->method('delete');

        foreach (range(0, 2) as $index) {
            try {
                $this->circuitBreaker->callService(
                    static function () {
                        try {
                            throw new Exception('test', 500);
                        } catch (Exception $exception) {
                            throw new CircuitBreakerException($exception);
                        }
                    },
                    'SERVICE'
                );
            } catch (Exception $exception) {
            }
        }
    }

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);

        $this->factoryMock = $this->createMock(ExceptionFactoryInterface::class);

        $this->circuitBreaker = new CircuitBreaker(
            $this->cacheMock,
            2,
            3,
            $this->factoryMock
        );
    }
}
