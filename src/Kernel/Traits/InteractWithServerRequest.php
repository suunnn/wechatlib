<?php

namespace WeChatLib\Kernel\Traits;

use WeChatLib\Kernel\HttpClient\RequestUtil;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

trait InteractWithServerRequest
{
    /**
     * @var ServerRequestInterface
     */
    protected $request = null;

    public function getRequest(): ServerRequestInterface
    {
        if (!$this->request) {
            $this->request = RequestUtil::createDefaultServerRequest();
        }

        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    public function setRequestFromSymfonyRequest(Request $symfonyRequest)
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->request = $psrHttpFactory->createRequest($symfonyRequest);

        return $this;
    }
}
