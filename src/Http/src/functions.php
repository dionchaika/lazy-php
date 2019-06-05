<?php

namespace Lazy\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('uri_for')) {
    /**
     * Get the URI for string.
     *
     * @param  string  $uri
     *
     * @return \Lazy\Http\Uri
     *
     * @throws \InvalidArgumentException
     */
    function uri_for(string $uri): Uri
    {
        return new Uri($uri);
    }
}

if (! function_exists('to_string')) {
    /**
     * Get the string
     * representation of the HTTP message.
     *
     * @param  Psr\Http\Message\MessageInterface  $message
     *
     * @return string
     */
    function to_string(MessageInterface $message): string
    {
        if ($message instanceof RequestInterface) {
            $res = "{$message->getMethod()} {$message->getRequestTarget()} HTTP/{$message->getProtocolVersion()}\r\n";
            foreach (array_keys($message->getHeaders()) as $header) {
                if (0 === strcasecmp($header, 'cookie')) {
                    $cookie = implode('; ', $message->getHeader('Cookie'));
                    $res .= "{$header}: {$cookie}\r\n";
                } else {
                    $res .= "{$header}: {$message->getHeaderLine($header)}\r\n";
                }
            }
        } else if ($message instanceof ResponseInterface) {
            $res = "HTTP/{$message->getProtocolVersion()} {$message->getStatusCode()} {$message->getReasonPhrase()}\r\n";
            foreach (array_keys($message->getHeaders()) as $header) {
                if (0 === strcasecmp($header, 'set-cookie')) {
                    foreach ($message->getHeader('Set-Cookie') as $setCookie) {
                        $res .= "{$header}: {$setCookie}\r\n";
                    }
                } else {
                    $res .= "{$header}: {$message->getHeaderLine($header)}\r\n";
                }
            }
        }

        return "{$res}\r\n{$message->getBody()}";
    }
}
