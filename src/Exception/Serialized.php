<?php

declare(strict_types=1);

namespace Tleckie\CircuitBreaker\Exception;

use Exception;
use Serializable;
use function serialize;
use function unserialize;

/**
 * Class Serialized
 * @package Tleckie\CircuitBreaker\Exception
 */
class Serialized implements Serializable
{
    /** @var array */
    protected array $data = [
        'message' => '',
        'code' => 0,
        'className' => '',
        'trace' => []
    ];

    /**
     * Serialized constructor.
     * @param Exception|null $exception
     */
    public function __construct(Exception $exception = null)
    {
        if ($exception) {
            $this
                ->setMessage($exception->getMessage())
                ->setClassName($exception::class)
                ->setCode($exception->getCode())
                ->setTrace($exception->getTrace());
        }
    }

    /**
     * @param array $trace
     * @return $this
     */
    public function setTrace(array $trace): Serialized
    {
        $this->data['trace'] = $trace;

        return $this;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): Serialized
    {
        $this->data['code'] = $code;

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className): Serialized
    {
        $this->data['className'] = $className;

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): Serialized
    {
        $this->data['message'] = $message;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? '';
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->data['code'];
    }

    /**
     * @return array
     */
    public function getTrace(): array
    {
        return $this->data['trace'];
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->data['className'];
    }

    /**
     * @return mixed
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($data): void
    {
        $this->data = unserialize($data);
    }
}
