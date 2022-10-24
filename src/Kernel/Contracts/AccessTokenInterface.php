<?php

namespace WeChatLib\Kernel\Contracts;

interface AccessTokenInterface
{
    public function getKey(): string;

    public function setKey($key): AccessTokenInterface;

    /**
     * @return string
     */
    public function getToken(): string;

    public function setToken(string $token);

    /**
     * @return array
     */
    public function toQuery(): array;
}
