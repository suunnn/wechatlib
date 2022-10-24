<?php

declare(strict_types=1);

namespace WeChatLib\Kernel\Contracts;

/**
 * @extends \ArrayAccess<string, mixed>
 */
interface ConfigInterface extends \ArrayAccess
{
    /**
     * @return array<string,mixed>
     */
    public function all(): array;
    public function has(string $key): bool;
    public function set(string $key, $value = null): void;

    /**
     * @param  array<string>|string  $key
     */
    public function get($key, $default = null);
}
