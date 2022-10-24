<?php

namespace WeChatLib\Kernel\Contracts;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface AccessTokenAwareHttpClientInterface extends HttpClientInterface
{
    public function withAccessToken(AccessTokenInterface $accessToken);
}
