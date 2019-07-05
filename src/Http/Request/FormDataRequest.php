<?php

namespace Lazy\Http\Request;

use Lazy\Http\Request;
use Lazy\Http\FormData;
use Psr\Http\Message\RequestInterface;

class FormDataRequest extends Request implements RequestInterface
{
    /**
     * The multipart/form-data request constructor.
     *
     * @param  \Lazy\Http\FormData|mixed[]  $data
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($data,
                                $method = Method::GET,
                                $uri = null,
                                array $headers = [],
                                $protocolVersion = '1.1')
    {
        $data = ($data instanceof FormData)
            ? $data
            : new FormData($data);

        $body = $data->getStream();

        parent::__construct($method, $uri, $headers, $body, $protocolVersion);

        $this->setContentTypeHeader('multipart/form-data');
        $this->setContentLengthHeader((string) $body->getSize());
    }
}
