<?php

namespace Lazy\Client;

use Exception;
use Psr\Http\Message\RequestInterface;

class ClientException extends Exception implements ClientExceptionInterface
{
    /**
     * The client exception request.
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * The client exception constructor.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @param string  $message
     */
    public function __construct(RequestInterface $request, string $message = '')
    {
        $this->request = $request;
        parent::__construct($message);
    }

    /**
     * Get the client exception request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
