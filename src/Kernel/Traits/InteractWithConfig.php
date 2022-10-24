<?php

namespace WeChatLib\Kernel\Traits;

use WeChatLib\Kernel\Config;
use WeChatLib\Kernel\Contracts\ConfigInterface;

trait InteractWithConfig
{
    /**
     * @var ConfigInterface
     */
    protected  $config;

    /**
     * @param array<string,mixed>|ConfigInterface $config
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function __construct($config)
    {
        $this->config = \is_array($config) ? new Config($config) : $config;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }
}
