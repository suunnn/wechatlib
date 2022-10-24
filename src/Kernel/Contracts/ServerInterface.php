<?php

namespace WeChatLib\Kernel\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ServerInterface
{
    public function serve(): ResponseInterface;
}
