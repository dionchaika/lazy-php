<?php

namespace Lazy\Http;

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
     * Get a default "text/xml" body parser.
     *
     * @return callable
     */
    public function getDefaultXmlParser(): callable
    {
        return function (StreamInterface $body) {
            return simplexml_load_string($body);
        };
    }

    /**
     * Get a default "application/json" body parser.
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
     * Get a default "application/x-www-form-urlencoded" body parser.
     *
     * @return callable
     */
    public function getDefaultUrlencodedParser(): callable
    {
        return function (StreamInterface $body) {
            parse_str($body, $data);

            return $data;
        };
    }

    /**
     * Get a body media type.
     *
     * @return string
     */
    public function getMediaType()
    {
        return strtolower(trim(explode(';', $this->getHeaderLine(Header::CONTENT_TYPE))[0]));
    }
}
