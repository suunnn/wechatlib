<?php

namespace WeChatLib\Kernel\Traits;

use WeChatLib\Kernel\Exceptions\InvalidArgumentException;

trait InteractWithHandlers
{
    /**
     * @var array<int, array{hash: string, handler: callable}>
     */
    protected $handlers = [];

    /**
     * @return array<int, array{hash: string, handler: callable}>
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     *
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function with( $handler)
    {
        return $this->withHandler($handler);
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function withHandler( $handler)
    {
        $this->handlers[] = $this->createHandlerItem($handler);

        return $this;
    }

    /**
     * @param    $handler
     *
     * @return array{hash: string, handler: callable}
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function createHandlerItem( $handler): array
    {
        return [
            'hash' => $this->getHandlerHash($handler),
            'handler' => $this->makeClosure($handler),
        ];
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    protected function getHandlerHash( $handler): string
    {
        if (\is_string($handler)) {
            return $handler;
        } else if (\is_array($handler)) {
            return is_string($handler[0]) ? $handler[0].'::'.$handler[1] : get_class(
                $handler[0]
            ).$handler[1];
        } else if ($handler instanceof \Closure) {
            return \spl_object_hash($handler);
        } else {
            throw new InvalidArgumentException('Invalid handler: '.\gettype($handler));
        }
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    protected function makeClosure( $handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (class_exists($handler) && \method_exists($handler, '__invoke')) {
            /**
             * @psalm-suppress InvalidFunctionCall
             * @phpstan-ignore-next-line https://github.com/phpstan/phpstan/issues/5867
             */
            return function () use ($handler) {
                return (new $handler())(...\func_get_args());
            };
        }

        throw new InvalidArgumentException(sprintf('Invalid handler: %s.', $handler));
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function prepend( $handler)
    {
        return $this->prependHandler($handler);
    }

    /**
     *
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function prependHandler( $handler)
    {
        \array_unshift($this->handlers, $this->createHandlerItem($handler));

        return $this;
    }

    /**
     *
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function without( $handler)
    {
        return $this->withoutHandler($handler);
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function withoutHandler( $handler)
    {
        $index = $this->indexOf($handler);

        if ($index > -1) {
            unset($this->handlers[$index]);
        }

        return $this;
    }

    /**
     *
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function indexOf( $handler): int
    {
        foreach ($this->handlers as $index => $item) {
            if ($item['hash'] === $this->getHandlerHash($handler)) {
                return $index;
            }
        }

        return -1;
    }

    /**
     *
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function when( $value,  $handler)
    {
        if (\is_callable($value)) {
            $value = \call_user_func($value, $this);
        }

        if ($value) {
            return $this->withHandler($handler);
        }

        return $this;
    }

    public function handle( $result,  $payload = null)
    {
        $next = $result = \is_callable($result) ? $result : function ($p) use ($result) {
            return $result;
        };

        foreach (\array_reverse($this->handlers) as $item) {
            $next = function ($p) use ($item, $next, $result) {
                return $item['handler']($p, $next) ?? $result($p);
            };
        }

        return $next($payload);
    }

    /**
     *
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function has( $handler): bool
    {
        return $this->indexOf($handler) > -1;
    }
}
