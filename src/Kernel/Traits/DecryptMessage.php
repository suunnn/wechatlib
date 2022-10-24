<?php

namespace WeChatLib\Kernel\Traits;

use WeChatLib\Kernel\Encryptor;
use WeChatLib\Kernel\Exceptions\BadRequestException;
use WeChatLib\Kernel\Message;
use WeChatLib\Kernel\Support\Xml;

trait DecryptMessage
{
    /**
     * @throws \WeChatLib\Kernel\Exceptions\RuntimeException
     * @throws \WeChatLib\Kernel\Exceptions\BadRequestException
     */
    public function decryptMessage(Message $message, Encryptor $encryptor, string $signature, $timestamp, string $nonce): Message
    {
        $ciphertext = $message->Encrypt;

        $this->validateSignature($encryptor->getToken(), $ciphertext, $signature, $timestamp, $nonce);

        $decryptContent = $encryptor->decrypt(
            $ciphertext,
            $signature,
            $nonce,
            $timestamp
        );

        if (0 === stripos($decryptContent, '<')) {
            $attributes = Xml::parse($decryptContent);
        }

        $dataSet = json_decode($decryptContent, true);

        if (JSON_ERROR_NONE === json_last_error() && $decryptContent) {
            $attributes = $dataSet;
        }

        $message->merge($attributes ?? []);

        return $message;
    }

    /**
     * @throws \WeChatLib\Kernel\Exceptions\BadRequestException
     */
    protected function validateSignature(string $token, string $ciphertext, string $signature, $timestamp, string $nonce): void
    {
        if (empty($signature)) {
            throw new BadRequestException('Request signature must not be empty.');
        }

        $params = [$token, $timestamp, $nonce, $ciphertext];

        sort($params, SORT_STRING);

        if ($signature !== sha1(implode($params))) {
            throw new BadRequestException('Invalid request signature.');
        }
    }
}
