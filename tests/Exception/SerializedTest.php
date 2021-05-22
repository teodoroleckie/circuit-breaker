<?php

namespace Tleckie\CircuitBreaker\Tests\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Tleckie\CircuitBreaker\Exception\Serialized;

/**
 * Class SerializedTest
 * @package Tleckie\CircuitBreaker\Tests\Exception
 */
class SerializedTest extends TestCase
{
    /** @var Serialized */
    private Serialized $serialized;

    /** @var Exception */
    private Exception $exception;

    /**
     * @test
     */
    public function message(): void
    {
        static::assertEquals('test', $this->serialized->getMessage());
        static::assertInstanceOf(Serialized::class, $this->serialized->setMessage('test 2'));
        static::assertEquals('test 2', $this->serialized->getMessage());
    }

    /**
     * @test
     */
    public function code(): void
    {
        static::assertEquals(500, $this->serialized->getCode());
        static::assertInstanceOf(Serialized::class, $this->serialized->setCode(600));
        static::assertEquals(600, $this->serialized->getCode());
    }

    /**
     * @test
     */
    public function trace(): void
    {
        static::assertTrue(count($this->serialized->getTrace()) > 1);
        static::assertInstanceOf(Serialized::class, $this->serialized->setTrace(['test']));
        static::assertTrue(count($this->serialized->getTrace()) === 1);
    }

    /**
     * @test
     */
    public function className(): void
    {
        static::assertEquals(Exception::class, $this->serialized->getClassName());
        static::assertInstanceOf(Serialized::class, $this->serialized->setClassName('Test'));
        static::assertEquals('Test', $this->serialized->getClassName());
    }

    /**
     * @test
     */
    public function empty(): void
    {
        $serialized = new Serialized();

        static::assertEquals('', $serialized->getClassName());
        static::assertEquals(0, $serialized->getCode());
        static::assertEquals([], $serialized->getTrace());
        static::assertEquals('', $serialized->getMessage());
    }

    protected function setUp(): void
    {
        $this->exception = new Exception('test', 500);

        $this->serialized = new Serialized($this->exception);
    }
}
