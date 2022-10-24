<?php

namespace WeChatLib\Kernel\Traits;

use WeChatLib\Kernel\Encryptor;
use WeChatLib\Kernel\Exceptions\InvalidArgumentException;
use WeChatLib\Kernel\Message;
use WeChatLib\Kernel\Support\Xml;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

trait RespondMessage
{
    /**
     * @throws \WeChatLib\Kernel\Exceptions\RuntimeException
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    public function transformToReply($response, Message $message, ?Encryptor $encryptor = null): ResponseInterface
    {
        if (empty($response)) {
            return new Response(200, [], 'success');
        }

        return $this->createXmlResponse(
            array_filter(
                \array_merge(
                    [
                        'ToUserName' => $message->FromUserName,
                        'FromUserName' => $message->ToUserName,
                        'CreateTime' => \time(),
                    ],
                    $this->normalizeResponse($response)
                )
            ),
            $encryptor
        );
    }

    /**
     * @return array<string, mixed>
     * @throws \WeChatLib\Kernel\Exceptions\InvalidArgumentException
     */
    protected function normalizeResponse($response): array
    {
        if (\is_callable($response)) {
            $response = $response();
        }

        if (\is_array($response)) {
            if (!isset($response['MsgType'])) {
                throw new InvalidArgumentException('MsgType cannot be empty.');
            }

            return $response;
        }

        if (is_string($response) || is_numeric($response)) {
            return [
                'MsgType' => 'text',
                'Content' => $response,
            ];
        }

        throw new InvalidArgumentException(
            sprintf('Invalid Response type "%s".', gettype($response))
        );
    }

    /**
     * @param array<string, mixed> $attributes
     * @throws \WeChatLib\Kernel\Exceptions\RuntimeException
     */
    protected function createXmlResponse(array $attributes, ?Encryptor $encryptor = null): ResponseInterface
    {
        $xml = Xml::build($attributes);

        return new Response(200, ['Content-Type' => 'application/xml'], $encryptor ? $encryptor->encrypt($xml) : $xml);
    }
}
