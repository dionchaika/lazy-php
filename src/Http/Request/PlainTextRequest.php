<?php

namespace Lazy\Http\Request;

use Lazy\Http\Request;
use Lazy\Http\InjectHeaderTrait;

class PlainTextRequest extends Request
{
    use InjectHeaderTrait;

    /**
     * The plain text request constructor.
     *
     * @param  string  $plainText
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $plainText,
                                $method = Method::GET,
                                $uri = null,
                                array $headers = [],
                                $protocolVersion = '1.1')
    {
        parent::__construct($method, $uri, $headers, $plainText, $protocolVersion);

        $this->injectContentTypeHeader('text/plain');
        $this->injectContentLengthHeader(strlen($plainText));
    }
}
