<?php

namespace WeChatLib\OfficialAccount\Contracts;

interface AccountInterface
{
    public function getAppId();

    public function getSecret();

    public function getToken();

    public function getAesKey();
}
