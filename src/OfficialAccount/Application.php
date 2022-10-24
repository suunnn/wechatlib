<?php

namespace WeChatLib\OfficialAccount;

use Symfony\Component\HttpClient\HttpClient;
use WeChatLib\Kernel\Contracts\AccessTokenInterface;
use WeChatLib\Kernel\Contracts\ServerInterface;
use WeChatLib\Kernel\Encryptor;
use WeChatLib\Kernel\Exceptions\InvalidConfigException;
use WeChatLib\Kernel\HttpClient\AccessTokenAwareClient;
use WeChatLib\Kernel\Traits\InteractWithClient;
use WeChatLib\Kernel\Traits\InteractWithConfig;
use WeChatLib\Kernel\Traits\InteractWithServerRequest;
use WeChatLib\OfficialAccount\Contracts\AccountInterface;
use WeChatLib\OfficialAccount\Contracts\ApplicationInterface;

class Application implements ApplicationInterface
{
    use InteractWithConfig;
    use InteractWithClient;
    use InteractWithServerRequest;

    /**
     * @var AccessTokenInterface
     */
    protected $accessToken = null;

    /**
     * @var AccountInterface
     */
    protected $account = null;

    /**
     * @var ServerInterface
     */
    protected $server = null;

    /**
     * @var Encryptor
     */
    protected $encryptor = null;

    public function createClient(): AccessTokenAwareClient
    {
        return new AccessTokenAwareClient(HttpClient::create(), $this->getAccessToken());
    }

    public function getAccount(): AccountInterface
    {
        if (!$this->account) {
            $this->setAccount(new Account(
                (string)$this->config->get('app_id'), /** @phpstan-ignore-line */
                (string)$this->config->get('secret'), /** @phpstan-ignore-line */
                (string)$this->config->get('token'), /** @phpstan-ignore-line */
                (string)$this->config->get('aes_key')/** @phpstan-ignore-line */
            ));
        }
        return $this->account;
    }

    /**
     * @param AccountInterface $account
     * @return $this
     */
    public function setAccount(AccountInterface $account): Application
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @param AccessTokenInterface $accessToken
     * @return $this
     */
    public function setAccessToken(AccessTokenInterface $accessToken): Application
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return AccessTokenInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \WeChatLib\Kernel\Exceptions\HttpException
     */
    public function getAccessToken(): AccessTokenInterface
    {
        if (!$this->accessToken) {
            $this->setAccessToken(new AccessToken(
                $this->getAccount()->getAppId(),
                $this->getAccount()->getSecret()
            ));
        }
        return $this->accessToken;
    }

    /**
     * @return Server
     * @throws InvalidConfigException
     */
    public function getServer()
    {
        if (!$this->server) {
            $this->server = new Server(
                $this->getRequest(),
                $this->getAccount()->getAesKey() ? $this->getEncryptor() : null
            );
        }

        return $this->server;
    }

    /**
     * @param ServerInterface $server
     * @return $this
     */
    public function setServer(ServerInterface $server): Application
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return Encryptor
     * @throws InvalidConfigException
     */
    public function getEncryptor(): Encryptor
    {
        if (!$this->encryptor) {
            $token = $this->getAccount()->getToken();
            $aesKey = $this->getAccount()->getAesKey();

            if (empty($token) || empty($aesKey)) {
                throw new InvalidConfigException('token or aes_key cannot be empty.');
            }

            $this->encryptor = new Encryptor(
                $this->getAccount()->getAppId(),
                $token,
                $aesKey,
                $this->getAccount()->getAppId()
            );
        }

        return $this->encryptor;
    }

    /**
     * @param Encryptor $encryptor
     * @return $this
     */
    public function setEncryptor(Encryptor $encryptor): Application
    {
        $this->encryptor = $encryptor;

        return $this;
    }

}
