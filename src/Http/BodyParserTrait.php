<?php

namespace Lazy\Http;

use SimpleXMLElement;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method string getHeaderLine($name)
 */
trait BodyParserTrait
{
    /**
     * The array of registered body parsers.
     *
     * @var array
     */
    protected $parsers = [];

    /**
     * Register a new body parser.
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

    /**
     * Get the content MIME-type.
     *
     * @return string
     */
    public function getContentMimeType()
    {
        return strtolower(trim(explode(';', $this->getHeaderLine('Content-Type'))[0]));
    }
}
