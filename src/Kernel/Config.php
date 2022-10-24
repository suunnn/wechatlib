<?php

namespace WeChatLib\Kernel;

use WeChatLib\Kernel\Contracts\ConfigInterface;
use WeChatLib\Kernel\Exceptions\InvalidArgumentException;
use WeChatLib\Kernel\Support\Arr;

/**
 * @implements \ArrayAccess<mixed, mixed>
 */
class Config implements \ArrayAccess, ConfigInterface
{
    /**
     * @var array<string>
     */
    protected $requiredKeys = [];

    protected $items = [];

    /**
     * @param array<string, mixed> $items
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
        $this->checkMissingKeys();
    }

    public function has(string $key): bool
    {
        return Arr::has($this->items, $key);
    }

    /**
     * @param array<string>|string $key
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($this->items, $key, $default);
    }

    /**
     * @param array<string> $keys
     *
     * @return  array<string, mixed>
     */
    public function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = Arr::get($this->items, $key, $default);
        }

        return $config;
    }

    /**
     * @param string $key
     * @param mixed|null $value
     */
    public function set(string $key, $value = null): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * @return  array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function offsetExists($key): bool
    {
        return $this->has(\strval($key));
    }

    public function offsetGet($key)
    {
        return $this->get(\strval($key));
    }

    public function offsetSet($key, $value): void
    {
        $this->set(\strval($key), $value);
    }

    public function offsetUnset($key): void
    {
        $this->set(\strval($key), null);
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function checkMissingKeys(): bool
    {
        if (empty($this->requiredKeys)) {
            return true;
        }

        $missingKeys = [];

        foreach ($this->requiredKeys as $key) {
            if (!$this->has($key)) {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            throw new InvalidArgumentException(sprintf("\"%s\" cannot be empty.\r\n", \join(',', $missingKeys)));
        }

        return true;
    }
}
