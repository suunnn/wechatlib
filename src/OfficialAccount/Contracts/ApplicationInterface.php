<?php

namespace WeChatLib\OfficialAccount\Contracts;

use WeChatLib\Kernel\Contracts\AccessTokenInterface;
use WeChatLib\Kernel\HttpClient\AccessTokenAwareClient;

interface ApplicationInterface
{
    public function getClient(): AccessTokenAwareClient;

    public function getAccount(): AccountInterface;

    public function setAccount(AccountInterface $account);

    public function getAccessToken(): AccessTokenInterface;

    public function setAccessToken(AccessTokenInterface $accessToken);
}
