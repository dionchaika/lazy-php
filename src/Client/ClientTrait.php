<?php

namespace Lazy\Client;

use Throwable;
use Lazy\Http\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * @property mixed[] $config
 * @property \Lazy\Cookie\CookieStorage $cookieStorage
 * @property string $requestOrigin
 * @property int $redirectNumber
 * @property mixed[] $redirectsHistory
 */
trait ClientTrait
{
    /**
     * Send a request and return a response.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $this->prepareRequest($request);

        $this->requestOrigin = $request->getUri()->getAuthority();

        $socket = $this->getSocketForRequest($request);
    }

    /**
     * Prepare a request.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function prepareRequest(RequestInterface $request): RequestInterface
    {
        foreach ($this->config['headers'] as $name => $value) {
            if (! $request->hasHeader($name)) {
                $request = $request->withHeader($name, $value);
            }
        }

        if ($this->config['origin_header'] && ! $request->hasHeader('Origin')) {
            $request = $request->withHeader('Origin', $this->requestOrigin);
        }

        if ('' === $request->getMethod()) {
            $request = $request->withMethod('GET');
        }

        if ('' === $request->getProtocolVersion()) {
            $request = $request->withProtocolVersion('1.1');
        }

        if ('1.1' === $request->getProtocolVersion()) {
            $request = $request->withHeader('Connection', 'close');
        }

        if ('' === $request->getUri()->getScheme()) {
            $request = $request->withUri($request->getUri()->withScheme('http'));
        }

        if ('' === $request->getUri()->getHost()) {
            throw new RequestException($request, 'Invalid request URI! Host is not defined.');
        }

        if ($this->config['cookies']) {
            $this->cookieStorage->clearExpiredCookies();
            $request = $this->cookieStorage->includeToRequest($request);
        }

        if ($request->getBody()->getSize()) {
            if ('GET' === $request->getMethod() || 'HEAD' === $request->getMethod()) {
                throw new RequestException($request, 'Invalid request! Request with a "GET" or a "HEAD" method cannot contain a body.');
            }

            if (! $request->getBody()->isReadable()) {
                throw new RequestException($request, 'Invalid request! Body is not readable.');
            }

            if (! $request->hasHeader('Content-Length')) {
                $request = $request->withHeader('Content-Length', (string)$request->getBody()->getSize());
            }
        }

        return $request;
    }

    /**
     * Get the remote socket for the request.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @return resource
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function getSocketForRequest(RequestInterface $request)
    {
        if (isset($this->config['proxy'][$request->getUri()->getScheme()])) {
            try {
                $uri = new Uri($this->config['proxy'][$request->getUri()->getScheme()]);
            } catch (Throwable $e) {
                throw new ClientException($request, $e->getMessage());
            }

            if ('' === $uri->getHost()) {
                throw new ClientException($request, 'Invalid proxy URI! Host is not defined.');
            }

            [$port, $host, $scheme] = [

                $uri->getPort() ?? 8080,
                $uri->getHost(),
                ('' !== $uri->getScheme()) ?: 'http'

            ];
        } else {
            [$port, $host, $scheme] = [

                $request->getUri()->getPort(),
                $request->getUri()->getHost(),
                $request->getUri()->getScheme()

            ];
        }

        [$transport, $port] = [

            ('https' === $scheme) ? 'ssl' : 'tcp',
            $port ?? (('https' === $scheme) ? 443 : 80)

        ];

        $remoteSocket = "{$transport}://{$host}:{$port}";

        if (null !== $this->config['context']) {
            $context = $this->config['context'];
        } else {
            $contextOpts = $this->config['context_opts'];
            $contextParams = $this->config['context_params'];

            $context = stream_context_create($contextOpts, $contextParams);
        }

        $socket = stream_socket_client($remoteSocket, $errno, $errstr, $this->config['timeout'], \STREAM_CLIENT_CONNECT, $context);
        if (false === $socket) {
            throw new NetworkException($request, 'Remote socket connection error #'.$errno.'! '.$errstr.'.');
        }

        $timeoutParts = explode('.', (string)$this->config['timeout']);

        $timeoutSecs = (int)$timeoutParts[0];
        $timeoutMicrosecs = isset($timeoutParts[1]) ? (int)$timeoutParts[1] : 0;

        if (false === stream_set_timeout($socket, $timeoutSecs, $timeoutMicrosecs)) {
            throw new ClientException($request, 'Unable to set the remote socket timeout!');
        }
    }
}
