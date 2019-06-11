<?php

namespace Lazy\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
        
    }
}
