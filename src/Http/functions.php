<?php

namespace Lazy\Http;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('to_string')) {
    /**
     * Stringify the HTTP message.
     *
     * @param  \Psr\Http\Message\MessageInterface  $message
     * @return string
     */
    function to_string(MessageInterface $message): string
    {
        if ($message instanceof RequestInterface) {
            $str = sprintf("%s %s HTTP/%s\r\n", $message->getMethod(),
                                                $message->getRequestTarget(),
                                                $message->getProtocolVersion());

            foreach (array_keys($message->getHeaders()) as $name) {
                $value = (0 !== strcasecmp($name, 'cookie'))
                    ? $message->getHeaderLine($name)
                    : implode(';', $message->getHeader($name));

                $str .= sprintf("%s: %s\r\n", $name, $value);
            }
        } else if ($message instanceof ResponseInterface) {
            $str = sprintf('HTTP/%s %s %s', $message->getProtocolVersion(),
                                            $message->getStatusCode(),
                                            $message->getReasonPhrase());

            foreach (array_keys($message->getHeaders()) as $name) {
                if (0 === strcasecmp($name, 'set-cookie')) {
                    foreach ($message->getHeader($name) as $cookie) {
                        $str .= sprintf("%s: %s\r\n", $name, $cookie);
                    }
                } else {
                    $str .= sprintf("%s: %s\r\n", $name, $message->getHeaderLine($name));
                }
            }
        }

        return sprintf("%s\r\n%s", $str, $message->getBody());
    }
}

if (! function_exists('parse_request')) {
    /**
     * Parse a request.
     *
     * @param  string  $request
     * @return \Lazy\Http\Request
     *
     * @throws \InvalidArgumentException
     */
    function parse_request(string $request): Request
    {
        if (false === strpos($request, "\r\n\r\n")) {
            throw new InvalidArgumentException('Invalid request! Request must be compliant with the "RFC 7230" standart.');
        }

        $requestParts = explode("\r\n\r\n", $request, 2);

        $headers = explode("\r\n", $requestParts[0]);
        $body = $requestParts[1];

        $requestLineParts = array_filter(explode(' ', array_shift($headers), 3));
        if (3 !== count($requestLineParts) || !preg_match('/^HTTP\/\d\.\d$/', $requestLineParts[2])) {
            throw new InvalidArgumentException('Invalid request! Request must be compliant with the "RFC 7230" standart.');
        }

        $method = $requestLineParts[0];
        $requestTarget = $requestLineParts[1];
        $protocolVersion = explode('/', $requestLineParts[2], 2)[1];

        $request = (new Request)
            ->withMethod($method)
            ->withRequestTarget($requestTarget)
            ->withProtocolVersion($protocolVersion);

        $request->getBody()->write($body);

        foreach ($headers as $header) {
            $headerParts = explode(':', $header, 2);

            $headerName = $headerParts[0];
            if (0 === strcasecmp($headerName, 'cookie')) {
                $headerValues = array_map('trim', explode(';', $headerParts[1]));
            } else {
                $headerValues = array_map('trim', explode(',', $headerParts[1]));
            }

            $request = $request->withHeader($headerName, $headerValues);
        }

        if ('1.1' === $protocolVersion && ! $request->hasHeader('Host')) {
            throw new InvalidArgumentException('Invalid request! "HTTP/1.1" request must contain a "Host" header.');
        }

        return $request;
    }
}

if (! function_exists('parse_response')) {
    /**
     * Parse a response.
     *
     * @param  string  $response
     * @return \Lazy\Http\Response
     *
     * @throws \InvalidArgumentException
     */
    function parse_response(string $response): Response
    {
        if (false === strpos($response, "\r\n\r\n")) {
            throw new InvalidArgumentException('Invalid response! Response must be compliant with the "RFC 7230" standart.');
        }

        $responseParts = explode("\r\n\r\n", $response, 2);

        $headers = explode("\r\n", $responseParts[0]);
        $body = $responseParts[1];

        $statusLineParts = array_filter(explode(' ', array_shift($headers), 3));
        if (2 > count($statusLineParts) || !preg_match('/^HTTP\/\d\.\d$/', $statusLineParts[0])) {
            throw new InvalidArgumentException('Invalid response! Response must be compliant with the "RFC 7230" standart.');
        }

        $protocolVersion = explode('/', $statusLineParts[0], 2)[1];
        $statusCode = (int) $statusLineParts[1];
        $reasonPhrase = isset($statusLineParts[2]) ? $statusLineParts[2] : '';

        $response = (new Response)
            ->withStatus($statusCode, $reasonPhrase)
            ->withProtocolVersion($protocolVersion);

        $response->getBody()->write($body);

        foreach ($headers as $header) {
            $headerParts = explode(':', $header, 2);

            $headerName = $headerParts[0];
            if (0 === strcasecmp($headerName, 'set-cookie')) {
                $headerValues = $headerParts[1];
            } else {
                $headerValues = array_map('trim', explode(',', $headerParts[1]));
            }

            $response = $response->withAddedHeader($headerName, $headerValues);
        }

        return $response;
    }
}
