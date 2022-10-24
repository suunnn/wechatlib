<?php

namespace WeChatLib\Kernel\Traits;

use WeChatLib\Kernel\HttpClient\AccessTokenAwareClient;

trait InteractWithClient
{
    /** @var AccessTokenAwareClient */
    protected $client = null;

    public function getClient(): AccessTokenAwareClient
    {
        if (!$this->client) {
            $this->client = $this->createClient();
        }

        return $this->client;
    }

    abstract public function createClient(): AccessTokenAwareClient;
}
