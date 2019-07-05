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
}
