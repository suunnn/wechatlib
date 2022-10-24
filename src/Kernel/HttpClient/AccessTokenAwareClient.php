<?php

namespace WeChatLib\Kernel\HttpClient;

use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WeChatLib\Kernel\Contracts\AccessTokenAwareHttpClientInterface;
use WeChatLib\Kernel\Contracts\AccessTokenInterface;

class AccessTokenAwareClient implements AccessTokenAwareHttpClientInterface
{
    use AsyncDecoratorTrait;

    /**
     * @var AccessTokenInterface
     */
    protected $accessToken = null;

    public function __construct(HttpClientInterface $client = null, AccessTokenInterface $accessToken = null)
    {
        $this->client = $client ?? HttpClient::create();
        $this->accessToken = $accessToken;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->accessToken) {
            $options['query'] = array_merge($options['query'] ?? [], $this->accessToken->toQuery());
        }

        $options = RequestUtil::formatBody($options);

        return new Response($this->client->request($method, $url, $options));
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __call(string $name, array $arguments)
    {
        return $this->client->$name(...$arguments);
    }

    public function withAccessToken(AccessTokenInterface $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
