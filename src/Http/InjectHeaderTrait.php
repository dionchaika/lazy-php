<?php

namespace Lazy\Http;

/**
 * @method bool hasHeader($name)
 * @method self setHeader($name, $value)
 * @method \Psr\Http\Message\StreamInterface getBody()
 */
trait InjectHeaderTrait
{
    /**
     * Inject a Content-Type header to the HTTP message.
     *
     * @param  string  $type
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function injectContentTypeHeader(string $type): void
    {
        if (! $this->hasHeader('Content-Type')) {
            $this->setHeader('Content-Type', $type);
        }
    }

    /**
     * Inject a Content-Length header to the HTTP message.
     *
     * @param  int  $length
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function injectContentLengthHeader(int $length): void
    {
        if (! $this->hasHeader('Content-Length')) {
            $this->setHeader('Content-Length', (string) $length);
        }
    }
}
