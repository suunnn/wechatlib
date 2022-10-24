<?php

namespace WeChatLib\Kernel;

use ArrayAccess;
use WeChatLib\Kernel\Exceptions\BadRequestException;
use WeChatLib\Kernel\Support\Xml;
use WeChatLib\Kernel\Traits\HasAttributes;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property string $FromUserName
 * @property string $ToUserName
 * @property string $Encrypt
 * @implements \ArrayAccess<string,mixed>
 */
class Message implements ArrayAccess
{
    use HasAttributes;

    protected $originContent;

    /**
     * @param  array<string,string>  $attributes
     */
    final public function __construct(array $attributes = [], $originContent = '')
    {
        $this->attributes = $attributes;
        $this->originContent = $originContent;
    }

    /**
     * @param  \Psr\Http\Message\ServerRequestInterface  $request
     *
     * @return \WeChatLib\Kernel\Message
     * @throws \WeChatLib\Kernel\Exceptions\BadRequestException
     */
    public static function createFromRequest(ServerRequestInterface $request): Message
    {
        $attributes = self::format($originContent = strval($request->getBody()));

        return new static($attributes, $originContent);
    }

    /**
     * @return array<string,string>
     * @throws \WeChatLib\Kernel\Exceptions\BadRequestException
     */
    public static function format(string $originContent): array
    {
        if (0 === stripos($originContent, '<')) {
            $attributes = Xml::parse($originContent);
        }

        // Handle JSON format.
        $dataSet = json_decode($originContent, true);

        if (JSON_ERROR_NONE === json_last_error() && $originContent) {
            $attributes = $dataSet;
        }

        if (empty($attributes) || !is_array($attributes)) {
            throw new BadRequestException('Failed to decode request contents.');
        }

        return $attributes;
    }

    public function getOriginalContents(): string
    {
        return $this->originContent ?? '';
    }

    public function __toString()
    {
        return $this->toJson() ?: '';
    }
}
