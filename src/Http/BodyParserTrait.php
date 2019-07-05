<?php

namespace Lazy\Http;

use SimpleXMLElement;
use Psr\Http\Message\ServerRequestInterface;

trait BodyParserTrait
{
    /**
     * The array of registered body parsers.
     *
     * @var mixed[]
     */
    protected $parsers = [];

    /**
     * Register a new body parser for a MIME-type.
     *
     * @param  string  $mimeType
     * @param  callable  $callable
     * @return void
     */
    public function registerParser($mimeType, callable $callable)
    {
        $this->parsers[$mimeType] = $callable;
    }

    /**
     * Get a default XML body parser.
     *
     * @return callable
     */
    public function getDefaultXmlParser(): callable
    {
        return function (ServerRequestInterface $request): ServerRequestInterface {
            return $request->withParsedBody(
                new SimpleXMLElement($request->getBody())
            );
        };
    }

    /**
     * Get a default JSON body parser.
     *
     * @return callable
     */
    public function getDefaultJsonParser(): callable
    {
        return function (ServerRequestInterface $request): ServerRequestInterface {
            return $request->withParsedBody(
                json_decode($request->getBody())
            );
        };
    }

    /**
     * Get a default multipart/form-data body parser.
     *
     * @return callable
     */
    public function getDefaultFormDataParser(): callable
    {
        return function (ServerRequestInterface $request): ServerRequestInterface {
            preg_match('/boundary=([^\s]+)/', $request->getHeaderLine('Content-Type'), $matches);

            $boundary = trim($matches[1], '"');

            return $request->withParsedBody([]);
        };
    }

    /**
     * Get a default application/x-www-form-urlencoded body parser.
     *
     * @return callable
     */
    public function getDefaultUrlencodedParser(): callable
    {
        return function (ServerRequestInterface $request): ServerRequestInterface {
            return $request->withParsedBody(
                urldecode($request->getBody())
            );
        };
    }
}
