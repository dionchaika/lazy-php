<?php

namespace Lazy\Http;

use Lazy\Http\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * The PSR-7 multipart/form-data stream wrapper class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc2046#section-5.1
 */
class FormDataStream extends Stream implements StreamInterface
{
    /**
     * The multipart/form-data boundary charset.
     */
    const BOUNDARY_CHARSET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * The multipart/form-data stream boundary.
     *
     * @var string
     */
    protected $boundary;

    /**
     * The multipart/form-data stream constructor.
     *
     * @param  mixed[]  $parts
     * @param  string|null  $boundary
     */
    public function __construct(array $parts = [], $boundary = null)
    {
        parent::__construct(fopen('php://temp', 'r+'));

        foreach ($parts as $part) {
            foreach (['name', 'value'] as $key) {
                if (! array_key_exists($key, $part)) {
                    throw new InvalidArgumentException("Part key is not set: {$key}!");
                }

                $this->append(
                    $part['name'],
                    $part['value'],
                    isset($part['headers']) ? $part['headers'] : [],
                    isset($part['filename']) ? $part['filename'] : null
                );
            }
        }

        $this->boundary = $boundary ?? $this->generateBoundary();
    }

    /**
     * Get the multipart/form-data stream boundary.
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Append a new multipart/form-data part to this stream.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  mixed[]  $headers
     * @param  string|null  $filename
     * @return void
     */
    public function append($name, $value, array $headers = [], $filename = null)
    {
        if (! $this->hasHeader('Content-Disposition', $headers)) {
            $headers['Content-Disposition'] = (! $filename && '0' !== $filename)
                ? sprintf("form-data; name=\"%s\"", $name)
                : sprintf("form-data; name=\"%s\"; filename=\"%s\"", $name, basename($filename));
        }

        $length = strlen($value);

        if (! $this->hasHeader('Content-Length', $headers) && $length) {
            $headers['Content-Length'] = (string) $length;
        }

        $this->write($this->stringifyHeaders($headers));
        $this->write($value);
        $this->write("\r\n");
        $this->write($this->boundary);
    }

    /**
     * Generate a multipart/form-data boundary.
     *
     * @param  int  $length
     * @param  string  $prefix
     * @return string
     */
    public function generateBoundary($length = 16, $prefix = '----TheLazyPHPFormBoundary')
    {
        $randMin = 0;
        $randMax = strlen(static::BOUNDARY_CHARSET) - 1;

        $boundary = '';

        for ($i = 0; $i < $length; $i++) {
            $boundary .= static::BOUNDARY_CHARSET[rand($randMin, $randMax)];
        }

        return $prefix.$boundary;
    }

    /**
     * Check is the header exists in the array of headers.
     *
     * @param  string  $name
     * @param  mixed[]  $headers
     * @return bool
     */
    protected function hasHeader($name, array $headers)
    {
        return array_key_exists(
            strtolower($name), array_change_key_case($headers)
        );
    }

    /**
     * Stringify headers for the multipart/form-data stream.
     *
     * @param  mixed[]  $headers
     * @return string
     */
    protected function stringifyHeaders(array $headers)
    {
        $str = '';

        foreach ($headers as $name => $value) {
            $str .= sprintf("%s: %s\r\n", $name, implode(', ', (array) $value));
        }

        return sprintf("%s\r\n%s\r\n", $this->boundary, $str);
    }
}
