<?php

namespace WeChatLib\OfficialAccount;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WeChatLib\Kernel\Contracts\ServerInterface;
use WeChatLib\Kernel\Encryptor;
use WeChatLib\Kernel\HttpClient\RequestUtil;
use Nyholm\Psr7\Response;
use WeChatLib\Kernel\Message;
use WeChatLib\Kernel\ServerResponse;
use WeChatLib\Kernel\Traits\DecryptMessage;
use WeChatLib\Kernel\Traits\InteractWithHandlers;
use WeChatLib\Kernel\Traits\RespondMessage;

class Server implements ServerInterface
{
    use InteractWithHandlers;
    use RespondMessage;
    use DecryptMessage;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    public function __construct($request = null, $encryptor = null)
    {
        $this->request = $request ?? RequestUtil::createDefaultServerRequest();
        $this->encryptor = $encryptor;
    }

    public function serve(): ResponseInterface
    {
        if (!!($str = $this->request->getQueryParams()['echostr'] ?? '')) {
            return new Response(200, [], $str);
        }

        $message = $this->getRequestMessage($this->request);
        $query = $this->request->getQueryParams();

        if ($this->encryptor && !empty($query['msg_signature'])) {
            $this->prepend($this->decryptRequestMessage($query));
        }

        $response = $this->handle(new Response(200, [], 'success'), $message);

        if (!($response instanceof ResponseInterface)) {
            $response = $this->transformToReply($response, $message, $this->encryptor);
        }

        return ServerResponse::make($response);
    }

    /**
     * @throws \Throwable
     */
    public function addMessageListener(string $type, callable $handler): Server
    {
        $this->withHandler(
            function (Message $message, \Closure $next) use ($type, $handler) {
                return $message->MsgType === $type ? $handler($message, $next) : $next($message);
            }
        );

        return $this;
    }

    /**
     * @throws \Throwable
     */
    public function addEventListener(string $event, callable $handler): Server
    {
        $this->withHandler(
            function (Message $message, \Closure $next) use ($event, $handler) {
                return $message->Event === $event ? $handler($message, $next) : $next($message);
            }
        );

        return $this;
    }

    /**
     * @param array<string,string> $query
     * @psalm-suppress PossiblyNullArgument
     */
    protected function decryptRequestMessage(array $query): \Closure
    {
        return function (Message $message, \Closure $next) use ($query) {
            if (!$this->encryptor) {
                return null;
            }

            $this->decryptMessage(
                $message,
                $this->encryptor,
                $query['msg_signature'] ?? '',
                $query['timestamp'] ?? '',
                $query['nonce'] ?? ''
            );

            return $next($message);
        };
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\BadRequestException
     */
    public function getRequestMessage(?ServerRequestInterface $request = null): Message
    {
        return Message::createFromRequest($request ?? $this->request);
    }
}
