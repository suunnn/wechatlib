<?php

namespace WeChatLib\Kernel\HttpClient;

use ArrayAccess;
use WeChatLib\Kernel\Contracts\Arrayable;
use WeChatLib\Kernel\Contracts\Jsonable;
use WeChatLib\Kernel\Exceptions\BadMethodCallException;
use WeChatLib\Kernel\Exceptions\BadResponseException;
use WeChatLib\Kernel\Support\Xml;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @implements \ArrayAccess<array-key, mixed>
 * @see \Symfony\Contracts\HttpClient\ResponseInterface
 */
class Response implements Jsonable, Arrayable, ArrayAccess, ResponseInterface
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \WeChatLib\Kernel\Exceptions\BadResponseException
     */
    public function toArray(bool $throw = true): array
    {
        if ('' === $content = $this->response->getContent($throw)) {
            throw new BadResponseException('Response body is empty.');
        }

        $contentType = $this->getHeaderLine('content-type', $throw);

        if (\str_contains($contentType, 'text/xml') || \str_contains($contentType, 'application/xml') || \str_starts_with($content, '<xml>')) {
            try {
                return Xml::parse($content) ?? [];
            } catch (\Throwable $e) {
                throw new BadResponseException('Response body is not valid xml.', 400, $e);
            }
        }

        return $this->response->toArray($throw);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \WeChatLib\Kernel\Exceptions\BadResponseException
     */
    public function offsetExists( $offset): bool
    {
        return \array_key_exists($offset, $this->toArray());
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \WeChatLib\Kernel\Exceptions\BadResponseException
     */
    public function offsetGet( $offset)
    {
        return $this->toArray()[$offset] ?? null;
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\BadMethodCallException
     */
    public function offsetSet( $offset,  $value): void
    {
        throw new BadMethodCallException('Response is immutable.');
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\BadMethodCallException
     */
    public function offsetUnset( $offset): void
    {
        throw new BadMethodCallException('Response is immutable.');
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \WeChatLib\Kernel\Exceptions\BadResponseException
     */
    public function toJson()
    {
        return \json_encode($this->toArray(), \JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function __call(string $name, array $arguments)
    {
        return $this->response->{$name}(...$arguments);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getHeaders(bool $throw = true): array
    {
        return $this->response->getHeaders($throw);
    }

    public function getContent(bool $throw = true): string
    {
        return $this->response->getContent($throw);
    }

    public function cancel(): void
    {
        $this->response->cancel();
    }

    public function getInfo(string $type = null)
    {
        return $this->response->getInfo($type);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \WeChatLib\Kernel\Exceptions\BadResponseException
     */
    public function __toString(): string
    {
        return $this->toJson() ?: '';
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function hasHeader(string $name, bool $throw = true): bool
    {
        return isset($this->getHeaders($throw)[$name]);
    }

    /**
     * @return array<array-key, mixed>
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function getHeader(string $name, bool $throw = true): array
    {
        return $this->hasHeader($name, $throw) ? $this->getHeaders($throw)[$name] : [];
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function getHeaderLine(string $name, bool $throw = true): string
    {
        return $this->hasHeader($name, $throw) ? implode(',', $this->getHeader($name)) : '';
    }
}
