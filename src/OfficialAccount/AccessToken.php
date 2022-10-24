<?php

namespace WeChatLib\OfficialAccount;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpClient\HttpClient;
use WeChatLib\Kernel\Contracts\AccessTokenInterface;
use WeChatLib\Kernel\Exceptions\HttpException;

class AccessToken implements AccessTokenInterface
{
    protected $appId;
    protected $secret;
    protected $key;
    protected $token;

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws HttpException
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function __construct(string $appId, string $secret, $key = null)
    {
        $this->appId = $appId;
        $this->secret = $secret;
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key ?? $this->key = sprintf('access_token:%s', $this->appId);
    }

    public function setKey($key): AccessTokenInterface
    {
        $this->key = $key;

        return $this;
    }

    public function getToken(): string
    {
        if (!$this->token) {
            $this->setToken(($token = Cache::get($this->getKey())) ? $token : $this->refresh());
        }
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws HttpException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function toQuery(): array
    {
        return ['access_token' => $this->getToken()];
    }

    public function refresh(): string
    {
        $response = HttpClient::create()->request('GET',
            'https://api.weixin.qq.com/cgi-bin/token',
            [
                'query' => [
                    'grant_type' => 'client_credential',
                    'appid' => $this->appId,
                    'secret' => $this->secret,
                ]
            ]
        )->toArray(false);

        if (empty($response['access_token'])) {
            throw new HttpException('Failed to get access_token: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
        }

        Cache::set($this->getKey(), $response['access_token'], intval($response['expires_in']));

        return $response['access_token'];
    }


}
