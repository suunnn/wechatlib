<?php

namespace WeChatLib\OfficialAccount;

use WeChatLib\OfficialAccount\Contracts\AccountInterface;

class Account implements AccountInterface
{
    protected $appId;
    protected $secret;
    protected $token = null;
    protected $aesKey = null;

    public function __construct($appId, $secret, $token = null, $aesKey = null)
    {
        $this->appId = $appId;
        $this->secret = $secret;
        $this->token = $token;
        $this->aesKey = $aesKey;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getAesKey(): string
    {
        return $this->aesKey;
    }
}
