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
        if (array_key_exists($request->getUri()->getScheme(), $this->config['proxy'])) {
            try {
                $uri = new Uri($this->config['proxy'][$request->getUri()->getScheme()]);
            } catch (Throwable $e) {
                throw new ClientException($request, $e->getMessage());
            }

            $scheme = $uri->getScheme();
            if ('' === $scheme) {
                $scheme = 'http';
            }

            $host = $uri->getHost();
            if ('' === $host) {
                throw new ClientException($request, 'Invalid proxy URI! Host is not defined.');
            }

            $port = $uri->getPort() ?? 8080;
        } else {
            $scheme = $request->getUri()->getScheme();
            $host = $request->getUri()->getHost();

            $port = $request->getUri()->getPort();
            $port = $port ?? ('https' === $scheme) ? 443 : 80;
        }

        $transport = ('https' === $scheme) ? 'ssl' : 'tcp';
        $remoteSocket = "{$transport}://{$host}:{$port}";
    }
}
