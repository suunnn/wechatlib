<?php

namespace WeChatLib\Kernel\HttpClient;

use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WeChatLib\Kernel\Support\UserAgent;
use WeChatLib\Kernel\Support\Xml;

class RequestUtil
{
    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */

    public static function mergeDefaultRetryOptions(array $options): array
    {
        return \array_merge([
            'status_codes' => GenericRetryStrategy::DEFAULT_RETRY_STATUS_CODES,
            'delay' => 1000,
            'max_delay' => 0,
            'max_retries' => 3,
            'multiplier' => 2.0,
            'jitter' => 0.1,
        ], $options);
    }

    /**
     * @param array<string, array|mixed> $options
     *
     * @return array<string, array|mixed>
     */
    public static function formatDefaultOptions(array $options): array
    {
        $defaultOptions = \array_filter(
            $options,
            function ($key) {
                return \array_key_exists($key, HttpClientInterface::OPTIONS_DEFAULTS);
            },
            \ARRAY_FILTER_USE_KEY
        );

        /** @phpstan-ignore-next-line */
        if (!isset($options['headers']['User-Agent']) && !isset($options['headers']['user-agent'])) {
            /** @phpstan-ignore-next-line */
            $defaultOptions['headers']['User-Agent'] = UserAgent::create();
        }

        return $defaultOptions;
    }

    /**
     * @param  array<string, array<string,mixed>|mixed>  $options
     *
     * @return array<string, array|mixed>
     */
    public static function formatBody(array $options): array
    {
        if (isset($options['xml'])) {
            if (is_array($options['xml'])) {
                $options['xml'] = Xml::build($options['xml']);
            }

            if (!\is_string($options['xml'])) {
                throw new \InvalidArgumentException('The type of `xml` must be string or array.');
            }

            /** @phpstan-ignore-next-line */
            if (!isset($options['headers']['Content-Type']) && !isset($options['headers']['content-type'])) {
                /** @phpstan-ignore-next-line */
                $options['headers']['Content-Type'] = [$options['headers'][] = 'Content-Type: text/xml'];
            }

            $options['body'] = $options['xml'];
            unset($options['xml']);
        }

        if (isset($options['json'])) {
            if (is_array($options['json'])) {
                /** XXX: ????????? JSON ???????????????????????????????????????????????? encode ??? unicode */
                $options['json'] = \json_encode($options['json'], empty($options['json']) ? \JSON_FORCE_OBJECT : \JSON_UNESCAPED_UNICODE);
            }

            if (!\is_string($options['json'])) {
                throw new \InvalidArgumentException('The type of `json` must be string or array.');
            }

            /** @phpstan-ignore-next-line */
            if (!isset($options['headers']['Content-Type']) && !isset($options['headers']['content-type'])) {
                /** @phpstan-ignore-next-line */
                $options['headers']['Content-Type'] = [$options['headers'][] = 'Content-Type: application/json'];
            }

            $options['body'] = $options['json'];
            unset($options['json']);
        }

        return $options;
    }

    public static function createDefaultServerRequest(): \Psr\Http\Message\ServerRequestInterface
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

        $creator = new \Nyholm\Psr7Server\ServerRequestCreator(
             $psr17Factory,
             $psr17Factory,
             $psr17Factory,
             $psr17Factory
        );

        return $creator->fromGlobals();
    }
}
