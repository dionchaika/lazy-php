<?php

namespace Lazy\Http;

use SimpleXMLElement;
use Psr\Http\Message\StreamInterface;

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
     * @param  string  $mediaType
     * @param  callable  $callable
     * @return void
     */
    public function registerParser($mediaType, callable $callable)
    {
        $this->parsers[$mediaType] = $callable;
    }

    /**
     * Get a body media type.
     *
     * @return string
     */
    public function getMediaType()
    {
        return strtolower(trim(explode(';',
                                       $this->getHeaderLine('Content-Type'))[0]));
    }

    /**
     * Get a default XML body parser.
     *
     * @return callable
     */
    public function getDefaultXmlParser(): callable
    {
        return function (StreamInterface $body) {
            return new SimpleXMLElement($body);
        };
    }

    /**
     * Get a default JSON body parser.
     *
     * @return callable
     */
    public function getDefaultJsonParser(): callable
    {
        return function (StreamInterface $body) {
            return json_decode($body);
        };
    }

    /**
     * Get a default application/x-www-form-urlencoded body parser.
     *
     * @return callable
     */
    public function getDefaultUrlencodedParser(): callable
    {
        return function (StreamInterface $body) {
            return urldecode($body);
        };
    }
}
