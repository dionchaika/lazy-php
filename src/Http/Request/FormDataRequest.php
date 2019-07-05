<?php

namespace Lazy\Http\Request;

use Lazy\Http\Request;
use Psr\Http\Message\RequestInterface;

class FormDataRequest extends Request implements RequestInterface
{
    /**
     * The form-data request constructor.
     *
     * @param  mixed[]  $data
     * @param  string|null  $boundary
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data,
                                $boundary = null,
                                $method = Method::GET,
                                $uri = null,
                                array $headers = [],
                                $protocolVersion = '1.1')
    {

    }

    /**
     * Generate a form-data boundary.
     *
     * @param  string  $prefix
     * @return void
     */
    public function generateBoundary($prefix = '')
    {
        
    }
}
