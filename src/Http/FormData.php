<?php

namespace Lazy\Http;

use Throwable;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * The multipart/form-data model class.
 *
 * @see https://tools.ietf.org/html/rfc2046#section-5.1
 */
class FormData
{
    /**
     * The multipart/form-data boundary charset.
     */
    const BOUNDARY_CHARSET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * The array of multipart/form-data parts.
     *
     * @var array
     */
    protected $parts = [];

    /**
     * The multipart/form-data boundary.
     *
     * @var string
     */
    protected $boundary;

    /**
     * The multipart/form-data constructor.
     *
     * @param  array  $parts
     * @param  string|null  $boundary
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($parts = [], $boundary = null)
    {
        foreach ($parts as $part) {
            $this->append($part);
        }

        $this->boundary = $boundary ?? static::generateBoudary();
    }

    /**
     * Generate a multipart/form-data boundary.
     *
     * @param  int  $length
     * @param  string  $prefix
     * @return string
     */
    public static function generateBoudary($length = 16, $prefix = '----TheLazyPHPFormBoundary')
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
     * Get the multipart/form-data boundary.
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Append a new multipart/form-data part.
     *
     * @param  array  $part
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function append(array $part)
    {
        foreach (['name', 'contents'] as $key) {
            if (! array_key_exists($key, $part)) {
                throw new InvalidArgumentException("Part key is not set: {$key}!");
            }
        }

        extract($part);

        $this->parts[] = compact('name', 'contents', 'headers', 'filename');

        return $this;
    }

    /**
     * Get the multipart/form-data stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getStream(): StreamInterface
    {
        return create_stream((string) $this);
    }

    /**
     * Stringify the multipart/form-data.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }
}
