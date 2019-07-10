<?php

namespace Lazy\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('stringify')) {
    /**
     * Stringify the HTTP message.
     *
     * @param  \Psr\Http\Message\MessageInterface  $message
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    function stringify(MessageInterface $message): string
    {
        if ($message instanceof RequestInterface) {
            $str = sprintf(
                "%s %s HTTP/%s\r\n", $message->getMethod(), $message->getRequestTarget(), $message->getProtocolVersion()
            );

            foreach (array_keys($message->getHeaders()) as $name) {
                $value = (0 !== strcasecmp($name, 'cookie'))
                    ? $message->getHeaderLine($name)
                    : implode('; ', $message->getHeader($name));

                $str .= sprintf("%s: %s\r\n", $name, $value);
            }
        } else if ($message instanceof ResponseInterface) {
            $str = sprintf(
                "HTTP/%s %s %s\r\n", $message->getProtocolVersion(), $message->getStatusCode(), $message->getReasonPhrase()
            );

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
     * @throws \RuntimeException
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

        if (
            3 !== count($requestLineParts) ||
            ! preg_match('/^HTTP\/(\d\.\d)$/', $requestLineParts[2], $matches)
        ) {
            throw new InvalidArgumentException('Invalid request! Request must be compliant with the "RFC 7230" standart.');
        }

        $method = $requestLineParts[0];
        $requestTarget = $requestLineParts[1];
        $protocolVersion = $matches[1];

        $request = (new Request)
            ->withMethod($method)
            ->withRequestTarget($requestTarget)
            ->withProtocolVersion($protocolVersion);

        $request->getBody()->write($body);

        foreach ($headers as $header) {
            $headerParts = explode(':', $header, 2);

            $name = $headerParts[0];

            $delim = (0 === strcasecmp($name, 'cookie')) ? ';' : ',';

            $request = $request->withHeader($name, array_map('trim', explode($delim, $headerParts[1])));
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
     * @throws \RuntimeException
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

        if (
            2 > count($statusLineParts) ||
            ! preg_match('/^HTTP\/(\d\.\d)$/', $statusLineParts[0], $matches)
        ) {
            throw new InvalidArgumentException('Invalid response! Response must be compliant with the "RFC 7230" standart.');
        }

        $protocolVersion = $matches[1];
        $statusCode = (int) $statusLineParts[1];
        $reasonPhrase = isset($statusLineParts[2]) ? $statusLineParts[2] : '';

        $response = (new Response)
            ->withStatus($statusCode, $reasonPhrase)
            ->withProtocolVersion($protocolVersion);

        $response->getBody()->write($body);

        foreach ($headers as $header) {
            $headerParts = explode(':', $header, 2);

            $name = $headerParts[0];

            $value = (0 === strcasecmp($name, 'set-cookie'))
                ? $headerParts[1]
                : array_map('trim', explode(',', $headerParts[1]));

            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }
}

if (! function_exists('create_stream')) {
    /**
     * Create a stream for the resource.
     *
     * Allowed stream options:
     *      1. size (int) - the stream size.
     *      2. seekable (bool) - is the stream seekable.
     *      3. readable (bool) - is the stream readable.
     *      4. writable (bool) - is the stream writable.
     *
     * @param  \Psr\Http\Message\StreamInterface|callable|resource|object|array|int|float|bool|string|null  $resource
     * @param  mixed[]  $opts
     * @return \Lazy\Http\Stream
     *
     * @throws \InvalidArgumentException
     */
    function create_stream($resource = '', array $opts = []): Stream
    {
        if (is_scalar($resource)) {
            $str = is_string($resource)
                ? $resource
                : (string) $resource;

            $resource = fopen('php://temp', 'r+');

            fwrite($resource, $str);

            return new Stream($resource, $opts);
        }

        if (is_array($resource)) {
            return create_stream(print_r($resource, true), $opts);
        }

        if (is_callable($resource)) {
            return create_stream(call_user_func($resource), $opts);
        }

        $type = gettype($resource);

        switch ($type) {
            case 'resource':
                return new Stream($resource, $opts);
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                }

                if (method_exists($resource, '__toString')) {
                    return create_stream((string) $resource, $opts);
                }

                break;
            case 'NULL':
                return new Stream(fopen('php://temp', 'r+'), $opts);
        }

        throw new InvalidArgumentException("Invalid type of the resource: {$type}!");
    }
}
